<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Exception;

/**
 * Initialize storage buckets for booking platform
 *
 * Professional bucket initialization supporting:
 * - Local filesystem
 * - MinIO (S3-compatible) with 'files-' prefix
 * - AWS S3 (single bucket with prefixes OR multiple buckets)
 * - DigitalOcean Spaces
 *
 * Features:
 * - Automatic bucket/directory creation
 * - Public/private access control
 * - CORS configuration for public buckets
 * - Lifecycle policies for temporary buckets
 * - Proper bucket naming conventions
 *
 * Usage:
 *   php artisan storage:init
 *   php artisan storage:init --driver=minio
 *   php artisan storage:init --dry-run
 *   php artisan storage:init --force
 */
class InitializeStorageBuckets extends Command
{
    protected $signature = 'storage:init
                            {--driver= : Storage driver to use (local, minio, s3, digitalocean)}
                            {--dry-run : Show what would be created without actually creating}
                            {--force : Force recreation of existing buckets}';

    protected $description = 'Initialize storage buckets/directories for the booking platform';

    private ?S3Client $s3Client = null;
    private array $stats = [
        'created' => 0,
        'skipped' => 0,
        'errors' => 0,
        'configured' => 0,
    ];

    public function handle()
    {
        try {
            $driver = $this->option('driver') ?? config('filesystems.default', 'local');
            $dryRun = $this->option('dry-run');
            $force = $this->option('force');

            $this->displayHeader($driver, $dryRun);

            // Get buckets configuration
            $buckets = config('filesystems.buckets', []);

            if (empty($buckets)) {
                $this->error('❌ No buckets defined in configuration');
                $this->newLine();
                $this->warn('💡 Quick Fix:');
                $this->line('1. Clear config cache: php artisan config:clear');
                $this->line('2. Check config/filesystems.php has "buckets" key');
                $this->line('3. Verify bucket definitions are correct');
                return Command::FAILURE;
            }

            $this->info("📦 Found " . count($buckets) . " buckets in configuration");
            $this->newLine();

            // Validate driver
            if (!$this->validateDriver($driver)) {
                return Command::FAILURE;
            }

            // Display strategy info
            $this->displayStrategyInfo($driver);

            // Process each bucket
            $this->processBuckets($buckets, $driver, $dryRun, $force);

            // Display summary
            $this->displaySummary($dryRun);

            return $this->stats['errors'] > 0 ? Command::FAILURE : Command::SUCCESS;
        } catch (Exception $e) {
            $this->error('❌ Failed to initialize buckets: ' . $e->getMessage());
            Log::error('Storage initialization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }

    private function displayHeader(string $driver, bool $dryRun): void
    {
        $this->info('🚀 Storage Initialization for Accommodation Booking Platform');
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->newLine();

        $this->line('Driver: ' . strtoupper($driver));
        $this->line('Default Disk: ' . config('filesystems.default', 'local'));
        $this->line('Environment: ' . config('app.env', 'local'));

        if ($dryRun) {
            $this->warn('Mode: DRY RUN (no changes will be made)');
        }

        $this->newLine();
    }

    private function displayStrategyInfo(string $driver): void
    {
        $strategy = match ($driver) {
            'local' => 'Individual directories in storage/app',
            'minio' => config('filesystems.disks.minio.use_single_bucket', false)
                ? 'Single bucket with prefixes'
                : 'Individual buckets with "files-" prefix',
            's3' => config('filesystems.disks.s3.use_single_bucket', false)
                ? 'Single bucket with prefixes'
                : 'Individual buckets',
            'digitalocean' => config('filesystems.disks.digitalocean.use_single_bucket', false)
                ? 'Single Space with prefixes'
                : 'Individual Spaces',
            default => 'Unknown strategy',
        };

        $this->info("📋 Strategy: {$strategy}");
        $this->newLine();
    }

    private function validateDriver(string $driver): bool
    {
        $validDrivers = ['local', 'minio', 's3', 'digitalocean'];

        if (!in_array($driver, $validDrivers)) {
            $this->error("❌ Invalid driver: $driver");
            $this->warn('Valid drivers: ' . implode(', ', $validDrivers));
            return false;
        }

        // For S3-based drivers, validate credentials
        if (in_array($driver, ['minio', 's3', 'digitalocean'])) {
            return $this->validateS3Credentials($driver);
        }

        return true;
    }

    private function validateS3Credentials(string $driver): bool
    {
        $config = $this->getDriverConfig($driver);

        if (!$config) {
            $this->error("❌ No configuration found for driver: $driver");
            return false;
        }

        if (empty($config['key']) || empty($config['secret'])) {
            $this->error("❌ Missing credentials for driver: $driver");
            $this->newLine();
            $this->warn('💡 Required environment variables:');

            match ($driver) {
                'minio' => [
                    $this->line('MINIO_ACCESS_KEY=your_access_key'),
                    $this->line('MINIO_SECRET_KEY=your_secret_key'),
                    $this->line('MINIO_ENDPOINT=http://127.0.0.1:9000'),
                    $this->line('MINIO_REGION=us-east-1'),
                    $this->line('MINIO_BUCKET=files (for single bucket strategy)'),
                ],
                's3' => [
                    $this->line('AWS_ACCESS_KEY_ID=your_access_key'),
                    $this->line('AWS_SECRET_ACCESS_KEY=your_secret_key'),
                    $this->line('AWS_DEFAULT_REGION=us-east-1'),
                    $this->line('AWS_BUCKET=your-bucket-name (for single bucket strategy)'),
                ],
                'digitalocean' => [
                    $this->line('DO_SPACES_KEY=your_access_key'),
                    $this->line('DO_SPACES_SECRET=your_secret_key'),
                    $this->line('DO_SPACES_REGION=nyc3'),
                    $this->line('DO_SPACES_BUCKET=your-space-name (for single bucket strategy)'),
                ],
            };

            return false;
        }

        return true;
    }

    private function getDriverConfig(string $driver): ?array
    {
        $diskConfig = config("filesystems.disks.{$driver}");

        if (!$diskConfig) {
            return null;
        }

        return [
            'key' => $diskConfig['key'] ?? null,
            'secret' => $diskConfig['secret'] ?? null,
            'region' => $diskConfig['region'] ?? 'us-east-1',
            'endpoint' => $diskConfig['endpoint'] ?? null,
            'bucket' => $diskConfig['bucket'] ?? null,
            'use_single_bucket' => $diskConfig['use_single_bucket'] ?? false,
            'use_path_style_endpoint' => $diskConfig['use_path_style_endpoint'] ?? false,
        ];
    }

    private function processBuckets(array $buckets, string $driver, bool $dryRun, bool $force): void
    {
        foreach ($buckets as $bucketName => $config) {
            $isPublic = is_array($config) ? ($config['public'] ?? false) : $config;
            $description = is_array($config) ? ($config['description'] ?? '') : '';
            $retention = is_array($config) ? ($config['retention'] ?? null) : null;

            $this->info("Processing: {$bucketName}");

            if ($description) {
                $this->line("  📝 {$description}");
            }

            try {
                if ($dryRun) {
                    $this->displayDryRunInfo($bucketName, $driver, $isPublic, $retention);
                    $this->stats['created']++;
                } else {
                    $result = $this->createBucket($bucketName, $driver, $isPublic, $force, $retention);

                    if ($result === 'created') {
                        $this->line("  ✅ Created successfully");
                        $this->stats['created']++;
                    } elseif ($result === 'exists') {
                        $this->line("  ℹ️  Already exists");
                        $this->stats['skipped']++;
                    } elseif ($result === 'configured') {
                        $this->line("  ⚙️  Configuration updated");
                        $this->stats['configured']++;
                    }
                }
            } catch (Exception $e) {
                $this->error("  ❌ Failed: " . $e->getMessage());
                Log::error("Failed to create bucket: {$bucketName}", [
                    'error' => $e->getMessage(),
                    'driver' => $driver,
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->stats['errors']++;
            }

            $this->newLine();
        }
    }

    private function displayDryRunInfo(string $bucketName, string $driver, bool $isPublic, ?int $retention): void
    {
        $actualBucketName = $this->getActualBucketName($bucketName, $driver);
        $visibility = $isPublic ? 'PUBLIC' : 'PRIVATE';
        $config = $this->getDriverConfig($driver);
        $useSingleBucket = $config['use_single_bucket'] ?? false;

        if ($useSingleBucket) {
            $this->line("  🪣 Main Bucket: {$actualBucketName}");
            $this->line("  📁 Prefix: {$bucketName}/");
        } else {
            $this->line("  🪣 Bucket: {$actualBucketName}");
        }

        $this->line("  👁️  Visibility: {$visibility}");

        if ($retention) {
            $this->line("  ⏰ Retention: {$retention} days");
        }
    }

    private function createBucket(
        string $bucketName,
        string $driver,
        bool $isPublic,
        bool $force,
        ?int $retention = null
    ): string {
        return match ($driver) {
            'local' => $this->createLocalDirectory($bucketName, $isPublic),
            'minio', 's3', 'digitalocean' => $this->createS3Bucket($bucketName, $driver, $isPublic, $force, $retention),
            default => throw new Exception("Unsupported driver: {$driver}"),
        };
    }

    private function createLocalDirectory(string $bucketName, bool $isPublic): string
    {
        $diskConfig = config("filesystems.disks.{$bucketName}");

        if (!$diskConfig) {
            throw new Exception("Disk configuration not found for: {$bucketName}");
        }

        $path = $diskConfig['root'] ?? storage_path("app/{$bucketName}");

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
            file_put_contents("{$path}/.gitignore", "*\n!.gitignore\n");
            $this->line("  📁 Created directory: {$path}");
            return 'created';
        }

        return 'exists';
    }

    private function createS3Bucket(
        string $bucketName,
        string $driver,
        bool $isPublic,
        bool $force,
        ?int $retention = null
    ): string {
        $client = $this->getS3Client($driver);

        if (!$client) {
            throw new Exception("Failed to initialize S3 client for driver: {$driver}");
        }

        $config = $this->getDriverConfig($driver);
        $useSingleBucket = $config['use_single_bucket'] ?? false;
        $actualBucketName = $this->getActualBucketName($bucketName, $driver);

        $this->line("  🪣 Target: {$actualBucketName}");

        // For single bucket strategy, ensure main bucket exists and configure prefix access
        if ($useSingleBucket) {
            $mainBucket = $config['bucket'];

            if (!$mainBucket) {
                throw new Exception("Main bucket not configured for single bucket strategy");
            }

            // Ensure main bucket exists
            if (!$this->bucketExists($client, $mainBucket)) {
                $this->createS3BucketPhysical($client, $mainBucket, $isPublic);
                $this->line("  ✅ Main bucket created: {$mainBucket}");
            }

            // Update configuration for the main bucket (only once, not per prefix)
            static $mainBucketConfigured = [];
            if (!isset($mainBucketConfigured[$mainBucket])) {
                $this->updateBucketConfiguration($client, $mainBucket, $isPublic, $retention);
                $mainBucketConfigured[$mainBucket] = true;
            }

            $this->line("  📁 Prefix configured: {$bucketName}/");
            return 'configured';
        }

        // Multiple buckets strategy
        $exists = $this->bucketExists($client, $actualBucketName);

        if ($exists && !$force) {
            $this->updateBucketConfiguration($client, $actualBucketName, $isPublic, $retention);
            return 'exists';
        }

        if ($exists && $force) {
            $this->warn("  ♻️  Recreating bucket (force mode)");
        }

        // Create the bucket
        $this->createS3BucketPhysical($client, $actualBucketName, $isPublic);
        $this->updateBucketConfiguration($client, $actualBucketName, $isPublic, $retention);

        return 'created';
    }

    private function createS3BucketPhysical(S3Client $client, string $bucketName, bool $isPublic): void
    {
        try {
            $params = ['Bucket' => $bucketName];

            // Only set ACL for MinIO (AWS S3 has different ACL handling)
            if (config('filesystems.default') === 'minio') {
                $params['ACL'] = $isPublic ? 'public-read' : 'private';
            }

            $client->createBucket($params);
            $this->line("  ✅ Bucket created");
        } catch (S3Exception $e) {
            if ($e->getAwsErrorCode() === 'BucketAlreadyOwnedByYou') {
                $this->line("  ℹ️  Bucket already owned by you");
            } else {
                throw $e;
            }
        }
    }

    private function getActualBucketName(string $bucketName, string $driver): string
    {
        $config = $this->getDriverConfig($driver);

        // For single bucket strategy, return the main bucket name
        if ($config['use_single_bucket'] ?? false) {
            return $config['bucket'];
        }

        // For multiple buckets, generate proper name based on driver
        return match ($driver) {
            'minio' => 'files-' . Str::replace('_', '-', $bucketName),
            's3', 'digitalocean' => Str::replace('_', '-', $bucketName),
            default => $bucketName,
        };
    }

    private function updateBucketConfiguration(
        S3Client $client,
        string $bucketName,
        bool $isPublic,
        ?int $retention
    ): void {
        // Set bucket policy for public buckets
        if ($isPublic) {
            try {
                $policy = $this->getPublicBucketPolicy($bucketName);
                $client->putBucketPolicy([
                    'Bucket' => $bucketName,
                    'Policy' => $policy,
                ]);
                $this->line("  🌐 Public access configured");
            } catch (S3Exception $e) {
                $this->warn("  ⚠️  Could not set public policy: " . $e->getMessage());
            }

            // Configure CORS - Skip for MinIO as it doesn't support S3 CORS API
            $driver = config('filesystems.default');
            if ($driver !== 'minio') {
                $this->configureCORS($client, $bucketName);
            } else {
                $this->line("  ℹ️  CORS skipped (use MinIO console for CORS configuration)");
            }
        }

        // Set lifecycle rules for retention
        if ($retention) {
            $this->configureLifecycle($client, $bucketName, $retention);
        }
    }

    private function configureCORS(S3Client $client, string $bucketName): void
    {
        try {
            $client->putBucketCors([
                'Bucket' => $bucketName,
                'CORSConfiguration' => [
                    'CORSRules' => [
                        [
                            'AllowedHeaders' => ['*'],
                            'AllowedMethods' => ['GET', 'HEAD', 'PUT', 'POST', 'DELETE'],
                            'AllowedOrigins' => ['*'],
                            'ExposeHeaders' => ['ETag', 'x-amz-meta-custom-header'],
                            'MaxAgeSeconds' => 3600,
                        ],
                    ],
                ],
            ]);
            $this->line("  ✅ CORS configured");
        } catch (S3Exception $e) {
            $this->warn("  ⚠️  Could not configure CORS: " . $e->getMessage());
        }
    }

    private function configureLifecycle(S3Client $client, string $bucketName, int $days): void
    {
        try {
            $client->putBucketLifecycleConfiguration([
                'Bucket' => $bucketName,
                'LifecycleConfiguration' => [
                    'Rules' => [
                        [
                            'Id' => 'auto-delete-old-files',
                            'Status' => 'Enabled',
                            'Expiration' => ['Days' => $days],
                            'Filter' => ['Prefix' => ''],
                        ],
                    ],
                ],
            ]);
            $this->line("  ⏰ Lifecycle: {$days}-day retention");
        } catch (S3Exception $e) {
            $this->warn("  ⚠️  Could not configure lifecycle: " . $e->getMessage());
        }
    }

    private function getPublicBucketPolicy(string $bucketName): string
    {
        $policy = [
            'Version' => '2012-10-17',
            'Statement' => [
                [
                    'Effect' => 'Allow',
                    'Principal' => ['AWS' => ['*']],
                    'Action' => ['s3:GetObject'],
                    'Resource' => ["arn:aws:s3:::{$bucketName}/*"],
                ],
            ],
        ];

        return json_encode($policy);
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

            if ($config['use_path_style_endpoint']) {
                $clientConfig['use_path_style_endpoint'] = true;
            }

            $this->s3Client = new S3Client($clientConfig);
            $this->line("  ✅ S3 client initialized");

            return $this->s3Client;
        } catch (Exception $e) {
            $this->error("  ❌ Failed to create S3 client: " . $e->getMessage());
            Log::error('S3 client initialization failed', [
                'driver' => $driver,
                'error' => $e->getMessage(),
            ]);
            return null;
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

    private function displaySummary(bool $dryRun): void
    {
        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════════');

        if ($dryRun) {
            $this->info('✨ Dry run completed - no changes made');
        } else {
            $this->info('✨ Storage initialization completed!');
        }

        $this->newLine();

        $this->table(
            ['Status', 'Count'],
            [
                ['Created', $this->stats['created']],
                ['Configured', $this->stats['configured']],
                ['Skipped', $this->stats['skipped']],
                ['Errors', $this->stats['errors']],
                ['Total', array_sum($this->stats)],
            ]
        );

        if ($this->stats['errors'] === 0) {
            $this->info('🎉 All buckets initialized successfully!');
        } else {
            $this->warn("⚠️  {$this->stats['errors']} bucket(s) failed to initialize");
            $this->line('Check logs for details: storage/logs/laravel.log');
        }
    }
}
