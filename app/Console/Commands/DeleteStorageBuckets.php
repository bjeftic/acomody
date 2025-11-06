<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Exception;

/**
 * Delete storage buckets for booking platform
 *
 * Professional bucket deletion supporting:
 * - Local filesystem
 * - MinIO (S3-compatible) with 'files-' prefix
 * - AWS S3 (single bucket with prefixes OR multiple buckets)
 * - DigitalOcean Spaces
 *
 * Features:
 * - Automatic bucket/directory deletion
 * - Dry run mode (preview only)
 * - Force option to skip confirmations
 * - Safe deletion with confirmation prompts
 *
 * Usage:
 *   php artisan storage:delete
 *   php artisan storage:delete --driver=minio
 *   php artisan storage:delete --dry-run
 *   php artisan storage:delete --force
 *   php artisan storage:delete --only=accommodation_photos,user_avatars
 */
class DeleteStorageBuckets extends Command
{
    protected $signature = 'storage:delete
                            {--driver= : Storage driver to use (local, minio, s3, digitalocean)}
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--force : Skip confirmation prompts}
                            {--only= : Comma-separated list of specific buckets to delete}';

    protected $description = 'Delete storage buckets/directories for the booking platform';

    private ?S3Client $s3Client = null;
    private array $stats = [
        'deleted' => 0,
        'skipped' => 0,
        'errors' => 0,
    ];

    public function handle()
    {
        try {
            $driver = $this->option('driver') ?? config('filesystems.driver', 'local');
            $dryRun = $this->option('dry-run');
            $force = $this->option('force');
            $only = $this->option('only');

            $this->displayHeader($driver, $dryRun);

            $buckets = config('filesystems.buckets', []);

            if (empty($buckets)) {
                $this->error('❌ No buckets defined in configuration');
                return Command::FAILURE;
            }

            // Filter buckets if --only option is provided
            if ($only) {
                $onlyBuckets = array_map('trim', explode(',', $only));
                $buckets = array_filter($buckets, function($key) use ($onlyBuckets) {
                    return in_array($key, $onlyBuckets);
                }, ARRAY_FILTER_USE_KEY);

                if (empty($buckets)) {
                    $this->error('❌ No matching buckets found for: ' . $only);
                    return Command::FAILURE;
                }

                $this->warn("⚠️  Only deleting: " . implode(', ', array_keys($buckets)));
                $this->newLine();
            }

            if (!$this->validateDriver($driver)) {
                return Command::FAILURE;
            }

            // Display strategy info
            $this->displayStrategyInfo($driver);

            // Show warning and get final confirmation if not in force or dry-run mode
            if (!$force && !$dryRun) {
                if (!$this->confirmDeletion(count($buckets))) {
                    $this->info('❌ Operation cancelled');
                    return Command::SUCCESS;
                }
            }

            $this->processBuckets($buckets, $driver, $dryRun, $force);

            $this->displaySummary($dryRun);

            return $this->stats['errors'] > 0 ? Command::FAILURE : Command::SUCCESS;

        } catch (Exception $e) {
            $this->error('❌ Failed to delete buckets: ' . $e->getMessage());
            Log::error('Storage deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }

    private function displayHeader(string $driver, bool $dryRun): void
    {
        $this->error('🗑️  Storage Bucket Deletion');
        $this->error('═══════════════════════════════════════════════════════════════');
        $this->newLine();

        $this->line('Driver: ' . strtoupper($driver));
        $this->line('Environment: ' . config('app.env', 'local'));

        if ($dryRun) {
            $this->warn('⚠️  Mode: DRY RUN (no deletions will occur)');
        } else {
            $this->error('⚠️  WARNING: This will PERMANENTLY delete buckets and all their contents!');
        }

        $this->newLine();
    }

    private function displayStrategyInfo(string $driver): void
    {
        $strategy = match($driver) {
            'local' => 'Delete directories from storage/app',
            'minio' => 'Delete buckets with "files-" prefix',
            's3' => config('filesystems.disks.s3.bucket') && env('AWS_USE_SINGLE_BUCKET', false)
                ? 'Delete prefixes from single bucket (or entire bucket with --force)'
                : 'Delete individual buckets',
            'digitalocean' => env('DO_USE_SINGLE_BUCKET', false)
                ? 'Delete prefixes from single Space (or entire Space with --force)'
                : 'Delete individual Spaces',
            default => 'Unknown strategy',
        };

        $this->info("📋 Strategy: {$strategy}");
        $this->newLine();
    }

    private function confirmDeletion(int $count): bool
    {
        $this->error("⚠️  You are about to delete {$count} bucket(s)!");
        $this->newLine();

        return $this->confirm(
            'Are you ABSOLUTELY sure you want to proceed? This action CANNOT be undone!',
            false
        );
    }

    private function validateDriver(string $driver): bool
    {
        $validDrivers = ['local', 'minio', 's3', 'digitalocean'];

        if (!in_array($driver, $validDrivers)) {
            $this->error("❌ Invalid driver: $driver");
            return false;
        }

        return true;
    }

    private function processBuckets(array $buckets, string $driver, bool $dryRun, bool $force): void
    {
        foreach ($buckets as $bucketName => $config) {
            $description = is_array($config) ? ($config['description'] ?? '') : '';

            $this->info("Processing: {$bucketName}");

            if ($description) {
                $this->line("  📝 {$description}");
            }

            $actualBucketName = $this->getActualBucketName($bucketName, $driver);
            $this->line("  🎯 Target: {$actualBucketName}");

            if (!$force && !$dryRun) {
                if (!$this->confirm("  Delete '{$actualBucketName}'?", false)) {
                    $this->line("  ⏭️  Skipped");
                    $this->stats['skipped']++;
                    $this->newLine();
                    continue;
                }
            }

            try {
                if ($dryRun) {
                    $this->line("  🧹 Would delete: {$actualBucketName}");
                    $this->stats['deleted']++;
                } else {
                    $this->deleteBucket($driver, $bucketName, $actualBucketName);
                    $this->line("  ✅ Deleted successfully");
                    $this->stats['deleted']++;
                }
            } catch (Exception $e) {
                $this->error("  ❌ Failed: " . $e->getMessage());
                Log::error("Failed to delete bucket: {$bucketName}", [
                    'driver' => $driver,
                    'actual_bucket' => $actualBucketName,
                    'error' => $e->getMessage(),
                ]);
                $this->stats['errors']++;
            }

            $this->newLine();
        }
    }

    private function deleteBucket(string $driver, string $bucketName, string $actualBucketName): void
    {
        match($driver) {
            'local' => $this->deleteLocalDirectory($bucketName),
            'minio', 's3', 'digitalocean' => $this->deleteS3Bucket($driver, $bucketName, $actualBucketName),
            default => throw new Exception("Unsupported driver: {$driver}"),
        };
    }

    private function deleteLocalDirectory(string $bucketName): void
    {
        $diskConfig = config("filesystems.disks.{$bucketName}");
        $path = $diskConfig['root'] ?? storage_path("app/{$bucketName}");

        if (is_dir($path)) {
            exec('rm -rf ' . escapeshellarg($path));
            $this->line("  🗑️  Directory removed: {$path}");
        } else {
            throw new Exception("Local directory not found: {$path}");
        }
    }

    private function deleteS3Bucket(string $driver, string $bucketName, string $actualBucketName): void
    {
        $client = $this->getS3Client($driver);

        if (!$client) {
            throw new Exception("Failed to initialize S3 client for driver: {$driver}");
        }

        $config = $this->getDriverConfig($driver);
        $useSingleBucket = $config['use_single_bucket'] ?? false;

        // For single bucket strategy, delete objects with prefix
        if ($useSingleBucket) {
            $this->deleteS3Prefix($client, $actualBucketName, $bucketName);
        } else {
            // For multiple buckets, delete the entire bucket
            $this->deleteS3BucketPhysical($client, $actualBucketName);
        }
    }

    private function deleteS3Prefix(S3Client $client, string $bucketName, string $prefix): void
    {
        $this->line("  🧹 Deleting objects with prefix: {$prefix}");

        // List and delete all objects with this prefix
        $continuationToken = null;
        $totalDeleted = 0;

        do {
            $params = [
                'Bucket' => $bucketName,
                'Prefix' => $prefix . '/',
            ];

            if ($continuationToken) {
                $params['ContinuationToken'] = $continuationToken;
            }

            $result = $client->listObjectsV2($params);

            if (!empty($result['Contents'])) {
                $keys = array_map(fn($obj) => ['Key' => $obj['Key']], $result['Contents']);

                $client->deleteObjects([
                    'Bucket' => $bucketName,
                    'Delete' => ['Objects' => $keys],
                ]);

                $totalDeleted += count($keys);
                $this->line("  📦 Deleted " . count($keys) . " objects");
            }

            $continuationToken = $result['IsTruncated'] ?? false
                ? ($result['NextContinuationToken'] ?? null)
                : null;

        } while ($continuationToken);

        $this->line("  ✅ Total objects deleted: {$totalDeleted}");
    }

    private function deleteS3BucketPhysical(S3Client $client, string $bucketName): void
    {
        // Check if bucket exists
        if (!$this->bucketExists($client, $bucketName)) {
            throw new Exception("Bucket does not exist: {$bucketName}");
        }

        // Empty the bucket first (S3 requirement)
        $this->line("  🧹 Emptying bucket...");

        $continuationToken = null;
        $totalDeleted = 0;

        do {
            $params = ['Bucket' => $bucketName];

            if ($continuationToken) {
                $params['ContinuationToken'] = $continuationToken;
            }

            $result = $client->listObjectsV2($params);

            if (!empty($result['Contents'])) {
                $keys = array_map(fn($obj) => ['Key' => $obj['Key']], $result['Contents']);

                $client->deleteObjects([
                    'Bucket' => $bucketName,
                    'Delete' => ['Objects' => $keys],
                ]);

                $totalDeleted += count($keys);
            }

            $continuationToken = $result['IsTruncated'] ?? false
                ? ($result['NextContinuationToken'] ?? null)
                : null;

        } while ($continuationToken);

        if ($totalDeleted > 0) {
            $this->line("  📦 Deleted {$totalDeleted} objects");
        }

        // Delete the bucket itself
        $client->deleteBucket(['Bucket' => $bucketName]);
        $this->line("  🗑️  Bucket deleted");
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

    private function getActualBucketName(string $bucketName, string $driver): string
    {
        $config = $this->getDriverConfig($driver);

        // For single bucket strategy, return the main bucket name
        if ($config['use_single_bucket'] ?? false) {
            return $config['bucket'];
        }

        // For multiple buckets, generate proper name
        return match($driver) {
            'minio' => 'files-' . Str::replace('_', '-', $bucketName),
            's3', 'digitalocean' => Str::replace('_', '-', $bucketName),
            default => $bucketName,
        };
    }

    private function getDriverConfig(string $driver): ?array
    {
        return match($driver) {
            'minio' => [
                'key' => config('filesystems.disks.minio.key'),
                'secret' => config('filesystems.disks.minio.secret'),
                'region' => config('filesystems.disks.minio.region'),
                'endpoint' => config('filesystems.disks.minio.endpoint'),
                'use_path_style_endpoint' => true,
            ],
            's3' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
                'region' => config('filesystems.disks.s3.region'),
                'endpoint' => config('filesystems.disks.s3.endpoint'),
                'bucket' => config('filesystems.disks.s3.bucket'),
                'use_single_bucket' => env('AWS_USE_SINGLE_BUCKET', false),
            ],
            'digitalocean' => [
                'key' => config('filesystems.disks.digitalocean.key'),
                'secret' => config('filesystems.disks.digitalocean.secret'),
                'region' => config('filesystems.disks.digitalocean.region'),
                'endpoint' => config('filesystems.disks.digitalocean.endpoint'),
                'bucket' => config('filesystems.disks.digitalocean.bucket'),
                'use_single_bucket' => env('DO_USE_SINGLE_BUCKET', false),
            ],
            default => null,
        };
    }

    private function getS3Client(string $driver): ?S3Client
    {
        if ($this->s3Client) {
            return $this->s3Client;
        }

        try {
            $config = $this->getDriverConfig($driver);

            $clientConfig = [
                'credentials' => [
                    'key' => $config['key'],
                    'secret' => $config['secret'],
                ],
                'region' => $config['region'],
                'version' => 'latest',
            ];

            if (!empty($config['endpoint'])) {
                $clientConfig['endpoint'] = $config['endpoint'];
            }

            if (isset($config['use_path_style_endpoint'])) {
                $clientConfig['use_path_style_endpoint'] = $config['use_path_style_endpoint'];
            }

            $this->s3Client = new S3Client($clientConfig);
            return $this->s3Client;

        } catch (Exception $e) {
            Log::error('S3 client initialization failed', [
                'driver' => $driver,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function displaySummary(bool $dryRun): void
    {
        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->newLine();

        if ($dryRun) {
            $this->info('✨ Dry run completed - no deletions made');
        } else {
            $this->info('🗑️  Deletion process completed!');
        }

        $this->newLine();

        $this->table(
            ['Status', 'Count'],
            [
                ['Deleted', $this->stats['deleted']],
                ['Skipped', $this->stats['skipped']],
                ['Errors', $this->stats['errors']],
                ['Total', array_sum($this->stats)],
            ]
        );

        if ($this->stats['errors'] === 0) {
            $this->info('🎉 All selected buckets deleted successfully!');
        } else {
            $this->warn("⚠️  {$this->stats['errors']} bucket(s) failed to delete");
            $this->line('Check logs for details: storage/logs/laravel.log');
        }
    }
}
