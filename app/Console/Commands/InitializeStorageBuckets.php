<?php

namespace App\Console\Commands;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Initialize storage buckets for booking platform
 *
 * Supports:
 * - Local filesystem (individual directories)
 * - MinIO / AWS S3 / DigitalOcean Spaces — single-bucket with prefixes OR individual buckets
 *
 * Usage:
 *   php artisan storage:init
 *   php artisan storage:init --dry-run
 *   php artisan storage:init --force
 */
class InitializeStorageBuckets extends Command
{
    protected $signature = 'storage:init
                            {--dry-run : Show what would be created without actually creating}
                            {--force : Force recreation of existing buckets (multi-bucket only)}';

    protected $description = 'Initialize storage buckets/directories for the booking platform';

    private ?S3Client $s3Client = null;

    private array $stats = [
        'created' => 0,
        'skipped' => 0,
        'errors' => 0,
        'configured' => 0,
    ];

    public function handle(): int
    {
        $driver = config('filesystems.driver', 'local');
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $buckets = config('filesystems.buckets', []);

        $this->info('Storage Initialization — '.strtoupper($driver));
        $this->line('Environment: '.config('app.env'));

        if ($dryRun) {
            $this->warn('Mode: DRY RUN — no changes will be made');
        }

        $this->newLine();

        if (empty($buckets)) {
            $this->error('No buckets defined in config/filesystems.php');

            return Command::FAILURE;
        }

        try {
            match ($driver) {
                'local' => $this->initializeLocal($buckets, $dryRun),
                'minio', 's3', 'digitalocean' => $this->initializeS3($buckets, $driver, $dryRun, $force),
                default => throw new Exception("Unsupported driver: {$driver}"),
            };
        } catch (Exception $e) {
            $this->error('Initialization failed: '.$e->getMessage());
            Log::error('Storage initialization failed', ['error' => $e->getMessage()]);

            return Command::FAILURE;
        }

        $this->displaySummary($dryRun);

        return $this->stats['errors'] > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    // ─── Local ───────────────────────────────────────────────────────────────

    private function initializeLocal(array $buckets, bool $dryRun): void
    {
        foreach (array_keys($buckets) as $bucketName) {
            $diskConfig = config("filesystems.disks.{$bucketName}");
            $path = $diskConfig['root'] ?? storage_path("app/{$bucketName}");

            $this->line("📁 {$bucketName} → {$path}");

            if ($dryRun) {
                $this->stats['created']++;

                continue;
            }

            if (is_dir($path)) {
                $this->line('   Already exists');
                $this->stats['skipped']++;

                continue;
            }

            mkdir($path, 0755, true);
            file_put_contents("{$path}/.gitignore", "*\n!.gitignore\n");
            $this->line('   ✅ Created');
            $this->stats['created']++;
        }
    }

    // ─── S3 / MinIO / DigitalOcean ────────────────────────────────────────────

    private function initializeS3(array $buckets, string $driver, bool $dryRun, bool $force): void
    {
        $driverConfig = $this->getDriverConfig($driver);

        if (! $driverConfig) {
            throw new Exception("No configuration found for driver: {$driver}");
        }

        if (empty($driverConfig['key']) || empty($driverConfig['secret'])) {
            throw new Exception("Missing credentials for driver: {$driver}");
        }

        if ($driverConfig['use_single_bucket']) {
            $this->initializeSingleBucket($buckets, $driver, $driverConfig, $dryRun);
        } else {
            $this->initializeMultiBucket($buckets, $driver, $dryRun, $force);
        }
    }

    /**
     * Single-bucket strategy: one physical bucket, each disk is a logical prefix.
     * Initializes the main bucket once and sets prefix-based lifecycle rules for
     * any disks that have a retention policy.
     */
    private function initializeSingleBucket(array $buckets, string $driver, array $driverConfig, bool $dryRun): void
    {
        $mainBucket = $driverConfig['bucket'];

        $this->line("Strategy: single bucket ({$mainBucket})");
        $this->newLine();

        // Collect lifecycle rules from all prefixes that have retention
        $lifecycleRules = [];
        foreach ($buckets as $bucketName => $config) {
            $retention = is_array($config) ? ($config['retention'] ?? null) : null;
            if ($retention) {
                $lifecycleRules[$bucketName] = $retention;
            }
        }

        // Display all prefixes
        foreach ($buckets as $bucketName => $config) {
            $isPublic = is_array($config) ? ($config['public'] ?? false) : $config;
            $retention = is_array($config) ? ($config['retention'] ?? null) : null;
            $visibility = $isPublic ? 'public' : 'private';
            $suffix = $retention ? " [{$retention}d retention]" : '';
            $this->line("   📁 {$bucketName}/ [{$visibility}]{$suffix}");
            $this->stats['configured']++;
        }

        $this->newLine();

        if ($dryRun) {
            $this->line("Would ensure bucket '{$mainBucket}' exists with public policy, CORS".(! empty($lifecycleRules) ? ', and prefix lifecycle rules' : ''));

            return;
        }

        $client = $this->getS3Client($driver);

        // Ensure main bucket exists
        if (! $this->bucketExists($client, $mainBucket)) {
            $this->createS3BucketPhysical($client, $mainBucket, $driver);
            $this->line("✅ Created main bucket: {$mainBucket}");
            $this->stats['created']++;
        } else {
            $this->line("ℹ️  Main bucket exists: {$mainBucket}");
            $this->stats['skipped']++;
        }

        // Public access policy
        $this->applyPublicPolicy($client, $mainBucket);

        // CORS (not supported by MinIO via API)
        if ($driver !== 'minio') {
            $this->applyCors($client, $mainBucket);
        }

        // Prefix-based lifecycle rules for disks with retention
        if (! empty($lifecycleRules)) {
            $this->applyPrefixLifecycle($client, $mainBucket, $lifecycleRules);
        }
    }

    /**
     * Multi-bucket strategy: one physical bucket per disk.
     */
    private function initializeMultiBucket(array $buckets, string $driver, bool $dryRun, bool $force): void
    {
        $this->line('Strategy: individual buckets');
        $this->newLine();

        $client = $dryRun ? null : $this->getS3Client($driver);

        foreach ($buckets as $bucketName => $config) {
            $isPublic = is_array($config) ? ($config['public'] ?? false) : $config;
            $retention = is_array($config) ? ($config['retention'] ?? null) : null;
            $actualName = $this->physicalBucketName($bucketName, $driver);

            $this->line("🪣 {$bucketName} → {$actualName}");

            if ($dryRun) {
                $this->line("   Would create [{$actualName}]".($retention ? " with {$retention}d lifecycle" : ''));
                $this->stats['created']++;

                continue;
            }

            try {
                $exists = $this->bucketExists($client, $actualName);

                if ($exists && ! $force) {
                    $this->line('   Already exists — updating configuration');
                    $this->stats['skipped']++;
                } else {
                    if ($exists) {
                        $this->warn('   Recreating (--force)');
                    }
                    $this->createS3BucketPhysical($client, $actualName, $driver);
                    $this->line('   ✅ Created');
                    $this->stats['created']++;
                }

                if ($isPublic) {
                    $this->applyPublicPolicy($client, $actualName);
                    if ($driver !== 'minio') {
                        $this->applyCors($client, $actualName);
                    }
                }

                if ($retention) {
                    $this->applyBucketLifecycle($client, $actualName, $retention);
                }
            } catch (Exception $e) {
                $this->error("   ❌ Failed: {$e->getMessage()}");
                Log::error("Failed to initialize bucket: {$bucketName}", ['error' => $e->getMessage()]);
                $this->stats['errors']++;
            }

            $this->newLine();
        }
    }

    // ─── S3 helpers ──────────────────────────────────────────────────────────

    private function createS3BucketPhysical(S3Client $client, string $bucketName, string $driver): void
    {
        $params = ['Bucket' => $bucketName];

        if ($driver === 'minio') {
            $params['ACL'] = 'public-read';
        }

        try {
            $client->createBucket($params);
        } catch (S3Exception $e) {
            if ($e->getAwsErrorCode() !== 'BucketAlreadyOwnedByYou') {
                throw $e;
            }
        }
    }

    private function applyPublicPolicy(S3Client $client, string $bucketName): void
    {
        $policy = json_encode([
            'Version' => '2012-10-17',
            'Statement' => [[
                'Effect' => 'Allow',
                'Principal' => ['AWS' => ['*']],
                'Action' => ['s3:GetObject'],
                'Resource' => ["arn:aws:s3:::{$bucketName}/*"],
            ]],
        ]);

        try {
            $client->putBucketPolicy(['Bucket' => $bucketName, 'Policy' => $policy]);
            $this->line('   🌐 Public access policy applied');
        } catch (S3Exception $e) {
            $this->warn('   ⚠️  Could not set public policy: '.$e->getMessage());
        }
    }

    private function applyCors(S3Client $client, string $bucketName): void
    {
        try {
            $client->putBucketCors([
                'Bucket' => $bucketName,
                'CORSConfiguration' => [
                    'CORSRules' => [[
                        'AllowedHeaders' => ['*'],
                        'AllowedMethods' => ['GET', 'HEAD', 'PUT', 'POST', 'DELETE'],
                        'AllowedOrigins' => ['*'],
                        'ExposeHeaders' => ['ETag'],
                        'MaxAgeSeconds' => 3600,
                    ]],
                ],
            ]);
            $this->line('   ✅ CORS configured');
        } catch (S3Exception $e) {
            $this->warn('   ⚠️  Could not configure CORS: '.$e->getMessage());
        }
    }

    /**
     * Prefix-based lifecycle rules for single-bucket strategy.
     * Each prefix with a retention policy gets its own rule.
     *
     * @param  array<string, int>  $prefixRetentionDays  bucketName → days
     */
    private function applyPrefixLifecycle(S3Client $client, string $bucketName, array $prefixRetentionDays): void
    {
        $rules = [];

        foreach ($prefixRetentionDays as $prefix => $days) {
            $rules[] = [
                'ID' => "expire-{$prefix}",
                'Status' => 'Enabled',
                'Filter' => ['Prefix' => "{$prefix}/"],
                'Expiration' => ['Days' => $days],
            ];
        }

        try {
            $client->putBucketLifecycleConfiguration([
                'Bucket' => $bucketName,
                'LifecycleConfiguration' => ['Rules' => $rules],
            ]);
            $prefixList = implode(', ', array_keys($prefixRetentionDays));
            $this->line("   ⏰ Lifecycle rules applied for: {$prefixList}");
        } catch (S3Exception $e) {
            $this->warn('   ⚠️  Could not configure lifecycle: '.$e->getMessage());
        }
    }

    /**
     * Bucket-level lifecycle rule for multi-bucket strategy.
     */
    private function applyBucketLifecycle(S3Client $client, string $bucketName, int $days): void
    {
        try {
            $client->putBucketLifecycleConfiguration([
                'Bucket' => $bucketName,
                'LifecycleConfiguration' => [
                    'Rules' => [[
                        'ID' => 'auto-expire',
                        'Status' => 'Enabled',
                        'Filter' => ['Prefix' => ''],
                        'Expiration' => ['Days' => $days],
                    ]],
                ],
            ]);
            $this->line("   ⏰ Lifecycle: {$days}-day retention");
        } catch (S3Exception $e) {
            $this->warn('   ⚠️  Could not configure lifecycle: '.$e->getMessage());
        }
    }

    private function bucketExists(S3Client $client, string $bucketName): bool
    {
        try {
            $client->headBucket(['Bucket' => $bucketName]);

            return true;
        } catch (S3Exception $e) {
            if ($e->getStatusCode() === 404) {
                return false;
            }
            throw $e;
        }
    }

    private function getS3Client(string $driver): S3Client
    {
        if ($this->s3Client) {
            return $this->s3Client;
        }

        $config = $this->getDriverConfig($driver);

        $clientConfig = [
            'credentials' => [
                'key' => $config['key'],
                'secret' => $config['secret'],
            ],
            'region' => $config['region'],
            'version' => 'latest',
        ];

        if (! empty($config['endpoint'])) {
            $clientConfig['endpoint'] = $config['endpoint'];
        }

        if ($config['use_path_style_endpoint']) {
            $clientConfig['use_path_style_endpoint'] = true;
        }

        return $this->s3Client = new S3Client($clientConfig);
    }

    private function getDriverConfig(string $driver): ?array
    {
        $disk = config("filesystems.disks.{$driver}");

        if (! $disk) {
            return null;
        }

        return [
            'key' => $disk['key'] ?? null,
            'secret' => $disk['secret'] ?? null,
            'region' => $disk['region'] ?? 'us-east-1',
            'endpoint' => $disk['endpoint'] ?? null,
            'bucket' => $disk['bucket'] ?? null,
            'use_single_bucket' => $disk['use_single_bucket'] ?? false,
            'use_path_style_endpoint' => $disk['use_path_style_endpoint'] ?? false,
        ];
    }

    private function physicalBucketName(string $bucketName, string $driver): string
    {
        return match ($driver) {
            'minio' => 'files-'.Str::replace('_', '-', $bucketName),
            default => Str::replace('_', '-', $bucketName),
        };
    }

    // ─── Summary ─────────────────────────────────────────────────────────────

    private function displaySummary(bool $dryRun): void
    {
        $this->newLine();
        $this->info($dryRun ? 'Dry run complete — no changes made.' : 'Initialization complete.');
        $this->table(
            ['Status', 'Count'],
            [
                ['Created', $this->stats['created']],
                ['Configured', $this->stats['configured']],
                ['Skipped', $this->stats['skipped']],
                ['Errors', $this->stats['errors']],
            ]
        );
    }
}
