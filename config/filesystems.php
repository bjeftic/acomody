<?php

use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Storage Configuration for Accommodation Booking Platform
|--------------------------------------------------------------------------
|
| This file configures storage buckets for an Airbnb-like platform.
| Supports: Local, MinIO, AWS S3, DigitalOcean Spaces
|
| Bucket Strategy:
| - Local: Individual directories
| - MinIO: Single bucket with prefixes OR individual buckets
| - S3/DO: Single bucket with prefixes OR individual buckets
|
*/

$driver = env('FILESYSTEM_DRIVER', env('FILESYSTEM_DISK', 'local'));
$env = env('APP_ENV', 'local');
$appUrl = env('APP_URL', 'http://localhost');
$appVersion = env('APP_VERSION', '1.0.0');

// Storage credentials
$storageKey = env('STORAGE_KEY', env('MINIO_ACCESS_KEY', 'minioadmin'));
$storageSecret = env('STORAGE_SECRET', env('MINIO_SECRET_KEY', 'minioadmin'));

// MinIO Configuration
$minioUseSingleBucket = env('MINIO_USE_SINGLE_BUCKET', false);
$minioMainBucket = env('MINIO_BUCKET', 'files');

// AWS S3 Configuration
$awsKey = env('AWS_ACCESS_KEY_ID');
$awsSecret = env('AWS_SECRET_ACCESS_KEY');
$awsRegion = env('AWS_DEFAULT_REGION', 'us-east-1');
$awsBucket = env('AWS_BUCKET', 'booking-platform');
$awsUrl = env('AWS_URL');
$awsEndpoint = env('AWS_ENDPOINT');
$awsUseSingleBucket = env('AWS_USE_SINGLE_BUCKET', false);

// AWS S3 Assets Configuration (separate bucket for static assets)
$awsKeyAssets = env('AWS_KEY_ASSETS', $awsKey);
$awsSecretAssets = env('AWS_SECRET_ASSETS', $awsSecret);
$awsBucketAssets = env('AWS_BUCKET_ASSETS', 'booking-platform-assets');

// DigitalOcean Spaces Configuration
$doUseSingleBucket = env('DO_USE_SINGLE_BUCKET', false);
$doRegion = env('DO_SPACES_REGION', 'nyc3');
$doBucket = env('DO_SPACES_BUCKET', 'booking-platform');

// Optional: CDN Configuration
$cdnUrl = env('CDN_URL');

// Asset disk configuration (for static assets like images, CSS, JS)
$assets = null;
if ($awsKeyAssets && $awsSecretAssets) {
    $assets = [
        'driver' => 's3',
        'key' => $awsKeyAssets,
        'secret' => $awsSecretAssets,
        'region' => $awsRegion,
        'bucket' => $awsBucketAssets,
        'url' => env('AWS_ASSETS_URL'),
        'visibility' => 'public',
        'throw' => false,
    ];
}

// CDN disk configuration
$cdn = null;
if ($cdnUrl) {
    $cdn = [
        'driver' => 's3',
        'key' => $awsKey,
        'secret' => $awsSecret,
        'region' => $awsRegion,
        'bucket' => $awsBucket,
        'url' => $cdnUrl,
        'visibility' => 'public',
        'throw' => false,
    ];
}

/*
|--------------------------------------------------------------------------
| Bucket Definitions
|--------------------------------------------------------------------------
|
| Define all storage buckets used in the application.
| Format: 'bucket_name' => [config] or 'bucket_name' => true/false (public/private)
|
*/

$buckets = [
    // ==========================================
    // ACCOMMODATION PHOTOS
    // ==========================================

    'accommodation_draft_photos' => [
        'public' => true,
        'description' => 'Temporary photos for accommodation drafts (pending approval)',
        'retention' => 30, // Auto-delete after 30 days
        'visibility' => 'public',
    ],

    'accommodation_photos' => [
        'public' => true,
        'description' => 'Approved accommodation photos (public access)',
        'visibility' => 'public',
    ],

    // ==========================================
    // USER CONTENT
    // ==========================================

    'user_profile_photos' => [
        'public' => true,
        'description' => 'User profile pictures',
        'visibility' => 'public',
    ],

    'user_documents' => [
        'public' => false,
        'description' => 'User identity documents (KYC)',
        'visibility' => 'private',
    ],

    // ==========================================
    // BOOKING RELATED
    // ==========================================

    'booking_receipts' => [
        'public' => false,
        'description' => 'Booking receipts and invoices',
        'retention' => 2555, // 7 years (legal requirement)
        'visibility' => 'private',
    ],

    'contracts' => [
        'public' => false,
        'description' => 'Rental agreements and contracts',
        'retention' => 2555, // 7 years
        'visibility' => 'private',
    ],

    // ==========================================
    // REVIEWS AND RATINGS
    // ==========================================

    'review_photos' => [
        'public' => true,
        'description' => 'Photos attached to guest reviews',
        'visibility' => 'public',
    ],

    // ==========================================
    // MESSAGING
    // ==========================================

    'message_attachments' => [
        'public' => false,
        'description' => 'Files sent through messaging system',
        'retention' => 365, // 1 year
        'visibility' => 'private',
    ],

    // ==========================================
    // PAYMENTS
    // ==========================================

    'payment_receipts' => [
        'public' => false,
        'description' => 'Payment transaction receipts',
        'retention' => 2555, // 7 years
        'visibility' => 'private',
    ],

    'payout_documents' => [
        'public' => false,
        'description' => 'Host payout documentation',
        'retention' => 2555, // 7 years
        'visibility' => 'private',
    ],

    // ==========================================
    // VERIFICATION
    // ==========================================

    'verification_documents' => [
        'public' => false,
        'description' => 'Host verification documents',
        'visibility' => 'private',
    ],

    // ==========================================
    // SUPPORT
    // ==========================================

    'support_attachments' => [
        'public' => false,
        'description' => 'Customer support ticket attachments',
        'retention' => 365, // 1 year
        'visibility' => 'private',
    ],

    'dispute_evidence' => [
        'public' => false,
        'description' => 'Evidence files for disputes',
        'retention' => 730, // 2 years
        'visibility' => 'private',
    ],

    // ==========================================
    // SYSTEM
    // ==========================================

    'temp' => [
        'public' => false,
        'description' => 'Temporary files for processing (e.g., queued uploads)',
        'retention' => 1, // Auto-delete after 1 day
        'visibility' => 'private',
    ],

    'backups' => [
        'public' => false,
        'description' => 'System and database backups',
        'visibility' => 'private',
    ],

    'exports' => [
        'public' => false,
        'description' => 'Data exports and reports',
        'retention' => 30,
        'visibility' => 'private',
    ],

    'logs' => [
        'public' => false,
        'description' => 'Application and system logs',
        'retention' => 90,
        'visibility' => 'private',
    ],

    // ==========================================
    // ADMIN AND REPORTS
    // ==========================================

    'admin_reports' => [
        'public' => false,
        'description' => 'Administrative reports and analytics',
        'retention' => 90,
        'visibility' => 'private',
    ],

    'tax_documents' => [
        'public' => false,
        'description' => 'Tax-related documents and reports',
        'visibility' => 'private',
    ],
];

/*
|--------------------------------------------------------------------------
| Generate Disk Configuration for Each Bucket
|--------------------------------------------------------------------------
*/

$bucketConfig = [];

foreach ($buckets as $bucketName => $config) {
    // Normalize config
    $isPublic = is_array($config) ? ($config['public'] ?? false) : $config;
    $description = is_array($config) ? ($config['description'] ?? '') : '';
    $retention = is_array($config) ? ($config['retention'] ?? null) : null;

    $disk = [];

    if ($isPublic) {
        $disk['visibility'] = 'public';
    }

    // Add metadata
    if ($description) {
        $disk['description'] = $description;
    }

    if ($retention) {
        $disk['retention_days'] = $retention;
    }

    // Configure based on driver
    if ($driver === 'local') {
        $disk += [
            'driver' => 'local',
            'root' => storage_path('app' . ($isPublic ? '/public' : '') . "/$bucketName"),
            'url' => $isPublic ? $appUrl . "/storage/$bucketName" : null,
            'visibility' => $isPublic ? 'public' : 'private',
            'throw' => false,
        ];
    }
    elseif ($driver === 'minio') {
        if ($minioUseSingleBucket) {
            // Single bucket strategy with prefixes
            $disk += [
                'driver' => 's3',
                'key' => $storageKey,
                'secret' => $storageSecret,
                'region' => env('MINIO_REGION', 'us-east-1'),
                'bucket' => $minioMainBucket,
                'prefix' => $bucketName,
                'url' => env('MINIO_URL', $appUrl . ':9000') . "/$minioMainBucket/$bucketName",
                'endpoint' => env('MINIO_ENDPOINT', 'http://minio:9000'),
                'use_path_style_endpoint' => true,
                'use_single_bucket' => true,
                'visibility' => $isPublic ? 'public' : 'private',
                'throw' => false,
            ];
        } else {
            // Multiple buckets strategy
            $actualBucketName = 'files-' . Str::replace('_', '-', $bucketName);
            $disk += [
                'driver' => 's3',
                'key' => $storageKey,
                'secret' => $storageSecret,
                'region' => env('MINIO_REGION', 'us-east-1'),
                'bucket' => $actualBucketName,
                'url' => env('MINIO_URL', $appUrl . ':9000') . "/$actualBucketName",
                'endpoint' => env('MINIO_ENDPOINT', 'http://minio:9000'),
                'use_path_style_endpoint' => true,
                'use_single_bucket' => false,
                'visibility' => $isPublic ? 'public' : 'private',
                'throw' => false,
            ];
        }
    }
    elseif ($driver === 's3') {
        if ($awsUseSingleBucket) {
            // Single bucket strategy with prefixes
            $disk += [
                'driver' => 's3',
                'key' => $awsKey,
                'secret' => $awsSecret,
                'region' => $awsRegion,
                'bucket' => $awsBucket,
                'prefix' => $bucketName,
                'url' => $awsUrl ? "$awsUrl/$bucketName" : null,
                'endpoint' => $awsEndpoint,
                'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
                'use_single_bucket' => true,
                'visibility' => $isPublic ? 'public' : 'private',
                'throw' => false,
            ];
        } else {
            // Multiple buckets strategy
            $disk += [
                'driver' => 's3',
                'key' => $awsKey,
                'secret' => $awsSecret,
                'region' => $awsRegion,
                'bucket' => $bucketName,
                'url' => $awsUrl ? str_replace($awsBucket, $bucketName, $awsUrl) : null,
                'endpoint' => $awsEndpoint,
                'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
                'use_single_bucket' => false,
                'visibility' => $isPublic ? 'public' : 'private',
                'throw' => false,
            ];
        }
    }
    elseif ($driver === 'digitalocean') {
        if ($doUseSingleBucket) {
            // Single Space strategy with prefixes
            $disk += [
                'driver' => 's3',
                'key' => env('DO_SPACES_KEY'),
                'secret' => env('DO_SPACES_SECRET'),
                'region' => $doRegion,
                'bucket' => $doBucket,
                'prefix' => $bucketName,
                'endpoint' => "https://$doRegion.digitaloceanspaces.com",
                'url' => env('DO_SPACES_URL') ? env('DO_SPACES_URL') . "/$bucketName" : null,
                'use_path_style_endpoint' => false,
                'use_single_bucket' => true,
                'visibility' => $isPublic ? 'public' : 'private',
                'throw' => false,
            ];
        } else {
            // Multiple Spaces strategy
            $disk += [
                'driver' => 's3',
                'key' => env('DO_SPACES_KEY'),
                'secret' => env('DO_SPACES_SECRET'),
                'region' => $doRegion,
                'bucket' => $bucketName,
                'endpoint' => "https://$doRegion.digitaloceanspaces.com",
                'url' => "https://{$bucketName}.{$doRegion}.digitaloceanspaces.com",
                'use_path_style_endpoint' => false,
                'use_single_bucket' => false,
                'visibility' => $isPublic ? 'public' : 'private',
                'throw' => false,
            ];
        }
    }

    $bucketConfig[$bucketName] = $disk;
}

/*
|--------------------------------------------------------------------------
| Return Filesystem Configuration
|--------------------------------------------------------------------------
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Storage Driver (for bucket initialization)
    |--------------------------------------------------------------------------
    */

    'driver' => $driver,

    /*
    |--------------------------------------------------------------------------
    | Buckets List (for storage:init and storage:delete commands)
    |--------------------------------------------------------------------------
    */

    'buckets' => $buckets,

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    */

    'disks' => array_merge(
        [
            /*
            |--------------------------------------------------------------------------
            | Local Disk - Private
            |--------------------------------------------------------------------------
            */
            'local' => [
                'driver' => 'local',
                'root' => storage_path('app/private'),
                'serve' => true,
                'throw' => false,
                'report' => false,
            ],

            /*
            |--------------------------------------------------------------------------
            | Public Disk
            |--------------------------------------------------------------------------
            */
            'public' => [
                'driver' => 'local',
                'root' => storage_path('app/public'),
                'url' => $appUrl . '/storage',
                'visibility' => 'public',
                'throw' => false,
                'report' => false,
            ],

            /*
            |--------------------------------------------------------------------------
            | MinIO Configuration (S3 Compatible)
            |--------------------------------------------------------------------------
            */
            'minio' => [
                'driver' => 's3',
                'key' => env('MINIO_ACCESS_KEY', 'minioadmin'),
                'secret' => env('MINIO_SECRET_KEY', 'minioadmin'),
                'region' => env('MINIO_REGION', 'us-east-1'),
                'bucket' => $minioMainBucket,
                'url' => env('MINIO_URL', $appUrl . ':9000'),
                'endpoint' => env('MINIO_ENDPOINT', 'http://minio:9000'),
                'use_path_style_endpoint' => true,
                'use_single_bucket' => $minioUseSingleBucket,
                'throw' => false,
                'visibility' => 'public',
            ],

            /*
            |--------------------------------------------------------------------------
            | AWS S3
            |--------------------------------------------------------------------------
            */
            's3' => [
                'driver' => 's3',
                'key' => $awsKey,
                'secret' => $awsSecret,
                'region' => $awsRegion,
                'bucket' => $awsBucket,
                'url' => $awsUrl,
                'endpoint' => $awsEndpoint,
                'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
                'use_single_bucket' => $awsUseSingleBucket,
                'throw' => false,
                'report' => false,
            ],

            /*
            |--------------------------------------------------------------------------
            | DigitalOcean Spaces
            |--------------------------------------------------------------------------
            */
            'digitalocean' => [
                'driver' => 's3',
                'key' => env('DO_SPACES_KEY'),
                'secret' => env('DO_SPACES_SECRET'),
                'region' => $doRegion,
                'bucket' => $doBucket,
                'endpoint' => "https://$doRegion.digitaloceanspaces.com",
                'use_path_style_endpoint' => false,
                'use_single_bucket' => $doUseSingleBucket,
                'throw' => false,
            ],
        ],

        // Add dynamically generated bucket configurations
        $bucketConfig,

        // Add assets and CDN if configured
        $assets ? ['assets' => $assets] : [],
        $cdn ? ['cdn' => $cdn] : []
    ),

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
