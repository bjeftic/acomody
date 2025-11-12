<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * List all configured storage buckets
 *
 * Features:
 * - Display all buckets with their configuration
 * - Show actual bucket names based on driver
 * - Filter by visibility (public/private)
 * - Show retention policies
 * - Export to JSON
 *
 * Usage:
 *   php artisan storage:list
 *   php artisan storage:list --driver=minio
 *   php artisan storage:list --public
 *   php artisan storage:list --private
 *   php artisan storage:list --json
 */
class ListStorageBuckets extends Command
{
    protected $signature = 'storage:list
                            {--driver= : Storage driver to display names for (local, minio, s3, digitalocean)}
                            {--public : Show only public buckets}
                            {--private : Show only private buckets}
                            {--json : Output as JSON}';

    protected $description = 'List all configured storage buckets for the booking platform';

    public function handle()
    {
        $driver = $this->option('driver') ?? config('filesystems.driver', 'local');
        $showPublic = $this->option('public');
        $showPrivate = $this->option('private');
        $jsonOutput = $this->option('json');

        $buckets = config('filesystems.buckets', []);

        if (empty($buckets)) {
            $this->error('❌ No buckets defined in configuration');
            return Command::FAILURE;
        }

        // Filter buckets by visibility if specified
        if ($showPublic || $showPrivate) {
            $buckets = array_filter($buckets, function($config, $name) use ($showPublic, $showPrivate) {
                $isPublic = is_array($config) ? ($config['public'] ?? false) : $config;

                if ($showPublic && $isPublic) return true;
                if ($showPrivate && !$isPublic) return true;

                return false;
            }, ARRAY_FILTER_USE_BOTH);
        }

        if ($jsonOutput) {
            $this->outputJson($buckets, $driver);
            return Command::SUCCESS;
        }

        $this->displayBuckets($buckets, $driver);
        return Command::SUCCESS;
    }

    private function displayBuckets(array $buckets, string $driver): void
    {
        $this->info('📦 Storage Buckets Configuration');
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->newLine();

        $this->line('Driver: ' . strtoupper($driver));
        $this->line('Default Disk: ' . config('filesystems.default', 'local'));
        $this->line('Total Buckets: ' . count($buckets));
        $this->newLine();

        $tableData = [];

        foreach ($buckets as $bucketName => $config) {
            $isPublic = is_array($config) ? ($config['public'] ?? false) : $config;
            $description = is_array($config) ? ($config['description'] ?? '-') : '-';
            $retention = is_array($config) ? ($config['retention'] ?? '-') : '-';
            $actualName = $this->getActualBucketName($bucketName, $driver);

            $visibility = $isPublic ? '🌐 Public' : '🔒 Private';
            $retentionDisplay = is_numeric($retention) ? "{$retention} days" : '-';

            $tableData[] = [
                $bucketName,
                $actualName,
                $visibility,
                $retentionDisplay,
                Str::limit($description, 40),
            ];
        }

        $this->table(
            ['Bucket Name', 'Actual Name', 'Visibility', 'Retention', 'Description'],
            $tableData
        );

        $this->newLine();
        $this->displayLegend($driver);
        $this->displayStatistics($buckets);
    }

    private function displayLegend(string $driver): void
    {
        $this->info('📋 Naming Convention:');

        match($driver) {
            'local' => $this->line('  • Local: storage/app/{bucket_name}'),
            'minio' => $this->line('  • MinIO: files-{bucket-name}'),
            's3' => env('AWS_USE_SINGLE_BUCKET', false)
                ? $this->line('  • AWS S3: Single bucket with prefixes')
                : $this->line('  • AWS S3: Individual buckets'),
            'digitalocean' => env('DO_USE_SINGLE_BUCKET', false)
                ? $this->line('  • DO Spaces: Single Space with prefixes')
                : $this->line('  • DO Spaces: Individual Spaces'),
            default => null,
        };

        $this->newLine();
    }

    private function displayStatistics(array $buckets): void
    {
        $stats = [
            'public' => 0,
            'private' => 0,
            'with_retention' => 0,
            'accommodation' => 0,
            'user' => 0,
            'booking' => 0,
            'system' => 0,
        ];

        foreach ($buckets as $bucketName => $config) {
            $isPublic = is_array($config) ? ($config['public'] ?? false) : $config;
            $retention = is_array($config) ? ($config['retention'] ?? null) : null;

            // Count by visibility
            if ($isPublic) {
                $stats['public']++;
            } else {
                $stats['private']++;
            }

            // Count with retention
            if ($retention) {
                $stats['with_retention']++;
            }

            // Count by category
            if (str_starts_with($bucketName, 'accommodation_')) {
                $stats['accommodation']++;
            } elseif (str_starts_with($bucketName, 'user_')) {
                $stats['user']++;
            } elseif (str_starts_with($bucketName, 'booking_') || str_starts_with($bucketName, 'payment_')) {
                $stats['booking']++;
            } elseif (in_array($bucketName, ['temp', 'backups', 'exports', 'logs'])) {
                $stats['system']++;
            }
        }

        $this->info('📊 Statistics:');
        $this->table(
            ['Category', 'Count'],
            [
                ['Total Buckets', count($buckets)],
                ['Public Buckets', $stats['public']],
                ['Private Buckets', $stats['private']],
                ['With Retention Policy', $stats['with_retention']],
                ['', ''],
                ['Accommodation', $stats['accommodation']],
                ['User', $stats['user']],
                ['Booking/Payment', $stats['booking']],
                ['System', $stats['system']],
            ]
        );
    }

    private function outputJson(array $buckets, string $driver): void
    {
        $output = [];

        foreach ($buckets as $bucketName => $config) {
            $isPublic = is_array($config) ? ($config['public'] ?? false) : $config;
            $description = is_array($config) ? ($config['description'] ?? null) : null;
            $retention = is_array($config) ? ($config['retention'] ?? null) : null;

            $output[] = [
                'name' => $bucketName,
                'actual_name' => $this->getActualBucketName($bucketName, $driver),
                'public' => $isPublic,
                'description' => $description,
                'retention_days' => $retention,
                'disk_config' => config("filesystems.disks.{$bucketName}"),
            ];
        }

        $this->line(json_encode([
            'driver' => $driver,
            'default_disk' => config('filesystems.default'),
            'total_buckets' => count($buckets),
            'buckets' => $output,
        ], JSON_PRETTY_PRINT));
    }

    private function getActualBucketName(string $bucketName, string $driver): string
    {
        $config = $this->getDriverConfig($driver);

        // For single bucket strategy, return prefix
        if ($config['use_single_bucket'] ?? false) {
            return ($config['bucket'] ?? 'main') . '/' . $bucketName;
        }

        // For multiple buckets, generate proper name
        return match($driver) {
            'minio' => 'files-' . Str::replace('_', '-', $bucketName),
            's3', 'digitalocean' => Str::replace('_', '-', $bucketName),
            'local' => $bucketName,
            default => $bucketName,
        };
    }

    private function getDriverConfig(string $driver): array
    {
        return match($driver) {
            'minio' => [
                'use_single_bucket' => false,
            ],
            's3' => [
                'bucket' => config('filesystems.disks.s3.bucket'),
                'use_single_bucket' => env('AWS_USE_SINGLE_BUCKET', false),
            ],
            'digitalocean' => [
                'bucket' => config('filesystems.disks.digitalocean.bucket'),
                'use_single_bucket' => env('DO_USE_SINGLE_BUCKET', false),
            ],
            default => [],
        };
    }
}
