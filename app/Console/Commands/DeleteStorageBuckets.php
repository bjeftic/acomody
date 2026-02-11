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
 */
class DeleteStorageBuckets extends Command
{
    protected $signature = 'storage:delete
                            {--driver= : Storage driver to use (local, minio, s3, digitalocean)}
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--force : Skip confirmation prompts}
                            {--only= : Comma-separated list of specific buckets to delete}
                            {--delete-main-bucket : Delete the entire main bucket (for single bucket strategy)}
                            {--scan : Scan and list all actual buckets in storage}
                            {--delete-all : Delete ALL buckets found in storage (DANGEROUS!)}';

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
            $driver = $this->option('driver') ?? config('filesystems.default', 'local');
            $dryRun = $this->option('dry-run');
            $force = $this->option('force');
            $only = $this->option('only');
            $deleteMainBucket = $this->option('delete-main-bucket');
            $scan = $this->option('scan');
            $deleteAll = $this->option('delete-all');

            $this->displayHeader($driver, $dryRun, $deleteMainBucket);

            if (!$this->validateDriver($driver)) {
                return Command::FAILURE;
            }

            // Scan mode - list all actual buckets
            if ($scan) {
                return $this->scanBuckets($driver);
            }

            // Delete all mode - delete everything found in storage
            if ($deleteAll) {
                return $this->deleteAllBuckets($driver, $dryRun, $force);
            }

            // Normal mode - use config
            $buckets = config('filesystems.buckets', []);

            if (empty($buckets)) {
                $this->error('❌ No buckets defined in configuration');
                $this->newLine();
                $this->warn('💡 Try using --scan to see actual buckets or --delete-all to delete everything');
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

            // Display strategy info
            $this->displayStrategyInfo($driver, $deleteMainBucket);

            // Show warning and get final confirmation
            if (!$force && !$dryRun) {
                if (!$this->confirmDeletion(count($buckets), $deleteMainBucket)) {
                    $this->info('❌ Operation cancelled');
                    return Command::SUCCESS;
                }
            }

            $this->processBuckets($buckets, $driver, $dryRun, $force, $deleteMainBucket);

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

    private function scanBuckets(string $driver): int
    {
        $this->info('🔍 Scanning for buckets...');
        $this->newLine();

        if ($driver === 'local') {
            $this->scanLocalBuckets();
        } else {
            $this->scanS3Buckets($driver);
        }

        return Command::SUCCESS;
    }

    private function scanLocalBuckets(): void
    {
        $path = storage_path('app');
        $directories = array_filter(glob($path . '/*'), 'is_dir');

        $this->table(
            ['Directory', 'Path'],
            array_map(fn($dir) => [basename($dir), $dir], $directories)
        );

        $this->newLine();
        $this->info('Total: ' . count($directories) . ' directories found');
    }

    private function scanS3Buckets(string $driver): void
    {
        $client = $this->getS3Client($driver);

        if (!$client) {
            $this->error('Failed to initialize S3 client');
            return;
        }

        try {
            $result = $client->listBuckets();
            $buckets = $result['Buckets'] ?? [];

            $tableData = [];
            foreach ($buckets as $bucket) {
                $name = $bucket['Name'];
                $created = $bucket['CreationDate']->format('Y-m-d H:i:s');

                // Try to get bucket size
                $size = $this->getBucketSize($client, $name);

                $tableData[] = [
                    $name,
                    $created,
                    $size
                ];
            }

            $this->table(
                ['Bucket Name', 'Created', 'Size'],
                $tableData
            );

            $this->newLine();
            $this->info('Total: ' . count($buckets) . ' buckets found');
            $this->newLine();
            $this->warn('💡 To delete all these buckets, run: php artisan storage:delete --delete-all');

        } catch (Exception $e) {
            $this->error('Failed to list buckets: ' . $e->getMessage());
        }
    }

    private function getBucketSize(S3Client $client, string $bucketName): string
    {
        try {
            $result = $client->listObjectsV2([
                'Bucket' => $bucketName,
                'MaxKeys' => 1000
            ]);

            $totalSize = 0;
            $objectCount = 0;

            if (isset($result['Contents'])) {
                foreach ($result['Contents'] as $object) {
                    $totalSize += $object['Size'];
                    $objectCount++;
                }
            }

            if ($totalSize === 0) {
                return 'Empty';
            }

            $sizeFormatted = $this->formatBytes($totalSize);
            return "{$objectCount} objects, {$sizeFormatted}";

        } catch (Exception $e) {
            return 'Unknown';
        }
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    private function deleteAllBuckets(string $driver, bool $dryRun, bool $force): int
    {
        $this->error('⚠️  DELETE ALL BUCKETS MODE');
        $this->error('═══════════════════════════════════════════════════════════════');
        $this->newLine();

        if ($driver === 'local') {
            $this->error('Delete all mode not supported for local driver');
            return Command::FAILURE;
        }

        $client = $this->getS3Client($driver);
        if (!$client) {
            $this->error('Failed to initialize S3 client');
            return Command::FAILURE;
        }

        try {
            $result = $client->listBuckets();
            $buckets = $result['Buckets'] ?? [];

            if (empty($buckets)) {
                $this->info('No buckets found');
                return Command::SUCCESS;
            }

            $this->warn("Found " . count($buckets) . " bucket(s):");
            foreach ($buckets as $bucket) {
                $this->line("  - " . $bucket['Name']);
            }
            $this->newLine();

            if (!$force && !$dryRun) {
                if (!$this->confirm('Delete ALL ' . count($buckets) . ' bucket(s)? This CANNOT be undone!', false)) {
                    $this->info('Operation cancelled');
                    return Command::SUCCESS;
                }
            }

            foreach ($buckets as $bucket) {
                $bucketName = $bucket['Name'];
                $this->info("Processing: {$bucketName}");

                try {
                    if ($dryRun) {
                        $this->line("  🧹 Would delete: {$bucketName}");
                        $this->stats['deleted']++;
                    } else {
                        $this->deleteS3BucketPhysical($client, $bucketName);
                        $this->line("  ✅ Deleted successfully");
                        $this->stats['deleted']++;
                    }
                } catch (Exception $e) {
                    $this->error("  ❌ Failed: " . $e->getMessage());
                    $this->stats['errors']++;
                }

                $this->newLine();
            }

            $this->displaySummary($dryRun);
            return $this->stats['errors'] > 0 ? Command::FAILURE : Command::SUCCESS;

        } catch (Exception $e) {
            $this->error('Failed to delete all buckets: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function displayHeader(string $driver, bool $dryRun, bool $deleteMainBucket): void
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

        if ($deleteMainBucket) {
            $this->error('⚠️  DELETE MAIN BUCKET MODE: The entire main bucket will be deleted!');
        }

        $this->newLine();
    }

    private function displayStrategyInfo(string $driver, bool $deleteMainBucket): void
    {
        $config = $this->getDriverConfig($driver);
        $useSingleBucket = $config['use_single_bucket'] ?? false;

        if ($useSingleBucket) {
            if ($deleteMainBucket) {
                $strategy = 'Delete entire main bucket with ALL prefixes';
            } else {
                $strategy = 'Delete only specified prefixes from main bucket';
            }
        } else {
            $strategy = match($driver) {
                'local' => 'Delete directories from storage/app',
                'minio' => 'Delete individual buckets',
                's3' => 'Delete individual buckets',
                'digitalocean' => 'Delete individual Spaces',
                default => 'Unknown strategy',
            };
        }

        $this->info("📋 Strategy: {$strategy}");
        $this->newLine();
    }

    private function confirmDeletion(int $count, bool $deleteMainBucket): bool
    {
        if ($deleteMainBucket) {
            $this->error("⚠️  You are about to delete the ENTIRE MAIN BUCKET with ALL its contents!");
            $this->newLine();
            return $this->confirm(
                'This will delete EVERYTHING in the main bucket. Are you ABSOLUTELY sure?',
                false
            );
        }

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

    private function processBuckets(
        array $buckets,
        string $driver,
        bool $dryRun,
        bool $force,
        bool $deleteMainBucket
    ): void {
        $config = $this->getDriverConfig($driver);
        $useSingleBucket = $config['use_single_bucket'] ?? false;

        // If deleting main bucket in single bucket strategy
        if ($useSingleBucket && $deleteMainBucket) {
            $this->deleteEntireMainBucket($driver, $dryRun);
            return;
        }

        foreach ($buckets as $bucketName => $bucketConfig) {
            $description = is_array($bucketConfig) ? ($bucketConfig['description'] ?? '') : '';

            $this->info("Processing: {$bucketName}");

            if ($description) {
                $this->line("  📝 {$description}");
            }

            $actualBucketName = $this->getActualBucketName($bucketName, $driver);

            if ($useSingleBucket) {
                $this->line("  🎯 Target: {$actualBucketName} (prefix: {$bucketName}/)");
            } else {
                $this->line("  🎯 Target: {$actualBucketName}");
            }

            if (!$force && !$dryRun) {
                $confirmMessage = $useSingleBucket
                    ? "  Delete all objects with prefix '{$bucketName}/' from '{$actualBucketName}'?"
                    : "  Delete '{$actualBucketName}'?";

                if (!$this->confirm($confirmMessage, false)) {
                    $this->line("  ⏭️  Skipped");
                    $this->stats['skipped']++;
                    $this->newLine();
                    continue;
                }
            }

            try {
                if ($dryRun) {
                    if ($useSingleBucket) {
                        $this->line("  🧹 Would delete prefix: {$bucketName}/ from {$actualBucketName}");
                    } else {
                        $this->line("  🧹 Would delete: {$actualBucketName}");
                    }
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
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->stats['errors']++;
            }

            $this->newLine();
        }
    }

    private function deleteEntireMainBucket(string $driver, bool $dryRun): void
    {
        $config = $this->getDriverConfig($driver);
        $mainBucket = $config['bucket'];

        if (!$mainBucket) {
            $this->error("❌ Main bucket not configured");
            $this->stats['errors']++;
            return;
        }

        $this->error("Deleting ENTIRE main bucket: {$mainBucket}");
        $this->newLine();

        try {
            if ($dryRun) {
                $this->line("  🧹 Would delete entire bucket: {$mainBucket}");
                $this->stats['deleted']++;
            } else {
                $client = $this->getS3Client($driver);
                if (!$client) {
                    throw new Exception("Failed to initialize S3 client");
                }

                $this->deleteS3BucketPhysical($client, $mainBucket);
                $this->line("  ✅ Main bucket deleted successfully");
                $this->stats['deleted']++;
            }
        } catch (Exception $e) {
            $this->error("  ❌ Failed to delete main bucket: " . $e->getMessage());
            $this->stats['errors']++;
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
            $this->deleteDirectory($path);
            $this->line("  🗑️  Directory removed: {$path}");
        } else {
            throw new Exception("Local directory not found: {$path}");
        }
    }

    private function deleteDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getRealPath());
            } else {
                unlink($item->getRealPath());
            }
        }

        rmdir($path);
    }

    private function deleteS3Bucket(string $driver, string $bucketName, string $actualBucketName): void
    {
        $client = $this->getS3Client($driver);

        if (!$client) {
            throw new Exception("Failed to initialize S3 client");
        }

        $config = $this->getDriverConfig($driver);
        $useSingleBucket = $config['use_single_bucket'] ?? false;

        if ($useSingleBucket) {
            $this->deleteS3Prefix($client, $actualBucketName, $bucketName);
        } else {
            $this->deleteS3BucketPhysical($client, $actualBucketName);
        }
    }

    private function deleteS3Prefix(S3Client $client, string $bucketName, string $prefix): void
    {
        $this->line("  🧹 Deleting objects with prefix: {$prefix}/");

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

            try {
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

            } catch (S3Exception $e) {
                $this->warn("  ⚠️  Error: " . $e->getMessage());
                break;
            }

        } while ($continuationToken);

        if ($totalDeleted > 0) {
            $this->line("  ✅ Total deleted: {$totalDeleted}");
        } else {
            $this->line("  ℹ️  No objects found");
        }
    }

    private function deleteS3BucketPhysical(S3Client $client, string $bucketName): void
    {
        if (!$this->bucketExists($client, $bucketName)) {
            throw new Exception("Bucket does not exist: {$bucketName}");
        }

        $this->line("  🧹 Emptying bucket...");

        $continuationToken = null;
        $totalDeleted = 0;

        do {
            $params = ['Bucket' => $bucketName];

            if ($continuationToken) {
                $params['ContinuationToken'] = $continuationToken;
            }

            try {
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

            } catch (S3Exception $e) {
                $this->warn("  ⚠️  Error emptying: " . $e->getMessage());
                break;
            }

        } while ($continuationToken);

        if ($totalDeleted > 0) {
            $this->line("  ✅ Emptied {$totalDeleted} objects");
        }

        try {
            $client->deleteBucket(['Bucket' => $bucketName]);
            $this->line("  🗑️  Bucket deleted");
        } catch (S3Exception $e) {
            throw new Exception("Failed to delete bucket: " . $e->getMessage());
        }
    }

    private function bucketExists(S3Client $client, string $bucketName): bool
    {
        try {
            $client->headBucket(['Bucket' => $bucketName]);
            return true;
        } catch (S3Exception $e) {
            return $e->getStatusCode() !== 404;
        }
    }

    private function getActualBucketName(string $bucketName, string $driver): string
    {
        $config = $this->getDriverConfig($driver);

        if ($config['use_single_bucket'] ?? false) {
            return $config['bucket'];
        }

        return match($driver) {
            'minio' => 'files-' . Str::replace('_', '-', $bucketName),
            's3', 'digitalocean' => Str::replace('_', '-', $bucketName),
            default => $bucketName,
        };
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
            return $this->s3Client;

        } catch (Exception $e) {
            Log::error('S3 client failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function displaySummary(bool $dryRun): void
    {
        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->newLine();

        if ($dryRun) {
            $this->info('✨ Dry run completed');
        } else {
            $this->info('🗑️  Deletion completed!');
        }

        $this->newLine();

        $this->table(
            ['Status', 'Count'],
            [
                ['Deleted', $this->stats['deleted']],
                ['Skipped', $this->stats['skipped']],
                ['Errors', $this->stats['errors']],
            ]
        );

        if ($this->stats['errors'] === 0 && $this->stats['deleted'] > 0) {
            $this->info('🎉 Success!');
        }
    }
}
