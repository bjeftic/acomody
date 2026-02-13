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

/*
|--------------------------------------------------------------------------
| Assets and CDN Configuration
|--------------------------------------------------------------------------
*/

$assets = [];
$cdn = [];

if ($appVersion) {
    if ($awsBucketAssets) {
        $assets = [
            'driver' => 's3',
            'root' => "/assets/$appVersion",
            'key' => $awsKeyAssets,
            'secret' => $awsSecretAssets,
            'region' => $awsRegion,
            'bucket' => $awsBucketAssets,
            'url' => env('AWS_ASSETS_URL'),
            'visibility' => 'public',
            'throw' => false,
        ];
    }

    if (env('AWS_CLOUDFRONT_URL')) {
        $cdn = [
            'driver' => 'cdn',
            'path' => "/assets/$appVersion",
            'base' => env('AWS_CLOUDFRONT_URL'),
        ];
    }
}

/*
|--------------------------------------------------------------------------
| Buckets Configuration for Booking Platform
|--------------------------------------------------------------------------
*/

$buckets = [
    // ==========================================
    // ACCOMMODATION RELATED BUCKETS
    // ==========================================

    'accommodation_draft_photos' => [
        'public' => true,
        'description' => 'Temporary photos during accommodation creation (auto-deleted after 7 days)',
        'retention' => 7,
        'visibility' => 'public',
    ],

    'accommodation_photos' => [
        'public' => true,
        'description' => 'Published accommodation photos (main listings)',
        'visibility' => 'public',
    ],

    'accommodation_thumbnails' => [
        'public' => true,
        'description' => 'Optimized thumbnails for accommodation photos',
        'visibility' => 'public',
    ],

    'accommodation_documents' => [
        'public' => false,
        'description' => 'Private documents (licenses, certificates, contracts)',
        'visibility' => 'private',
    ],

    'accommodation_floor_plans' => [
        'public' => true,
        'description' => 'Floor plans and layout diagrams',
        'visibility' => 'public',
    ],

    'accommodation_360_photos' => [
        'public' => true,
        'description' => '360-degree virtual tour photos',
        'visibility' => 'public',
    ],

    // ==========================================
    // USER RELATED BUCKETS
    // ==========================================

    'user_avatars' => [
        'public' => true,
        'description' => 'User profile photos and avatars',
        'visibility' => 'public',
    ],

    'user_documents' => [
        'public' => false,
        'description' => 'Private user documents',
        'visibility' => 'private',
    ],

    'user_verification_documents' => [
        'public' => false,
        'description' => 'Identity verification documents (passport, ID, etc.)',
        'retention' => 90,
        'visibility' => 'private',
    ],

    // ==========================================
    // BOOKING RELATED BUCKETS
    // ==========================================

    'booking_receipts' => [
        'public' => false,
        'description' => 'Booking receipts and confirmations',
        'visibility' => 'private',
    ],

    'booking_invoices' => [
        'public' => false,
        'description' => 'Payment invoices',
        'visibility' => 'private',
    ],

    'booking_contracts' => [
        'public' => false,
        'description' => 'Rental agreements and contracts',
        'visibility' => 'private',
    ],

    // ==========================================
    // REVIEWS AND RATINGS
    // ==========================================

    'review_photos' => [
        'public' => true,
        'description' => 'Guest review photos',
        'visibility' => 'public',
    ],

    'review_thumbnails' => [
        'public' => true,
        'description' => 'Thumbnails for review photos',
        'visibility' => 'public',
    ],

    // ==========================================
    // HOST/COMPANY RELATED
    // ==========================================

    'host_documents' => [
        'public' => false,
        'description' => 'Host verification and business documents',
        'visibility' => 'private',
    ],

    'company_documents' => [
        'public' => false,
        'description' => 'Company registration and legal documents',
        'visibility' => 'private',
    ],

    'host_verification_documents' => [
        'public' => false,
        'description' => 'Host identity and business verification',
        'retention' => 90,
        'visibility' => 'private',
    ],

    'company_logos' => [
        'public' => true,
        'description' => 'Company and property manager logos',
        'visibility' => 'public',
    ],

    // ==========================================
    // MESSAGING AND COMMUNICATION
    // ==========================================

    'message_attachments' => [
        'public' => false,
        'description' => 'Files attached to messages between guests and hosts',
        'retention' => 365,
        'visibility' => 'private',
    ],

    // ==========================================
    // PAYMENT RELATED
    // ==========================================

    'payment_receipts' => [
        'public' => false,
        'description' => 'Payment transaction receipts',
        'visibility' => 'private',
    ],

    'payment_disputes' => [
        'public' => false,
        'description' => 'Payment dispute documentation',
        'visibility' => 'private',
    ],

    // ==========================================
    // SYSTEM BUCKETS
    // ==========================================

    'temp' => [
        'public' => false,
        'description' => 'Temporary files (processing, uploads, etc.)',
        'retention' => 1,
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
            $disk += [
                'driver' => 's3',
                'key' => env('DO_SPACES_KEY'),
                'secret' => env('DO_SPACES_SECRET'),
                'region' => $doRegion,
                'bucket' => $doBucket,
                'prefix' => $bucketName,
                'endpoint' => "https://$doRegion.digitaloceanspaces.com",
                'url' => "https://$doBucket.$doRegion.digitaloceanspaces.com/$bucketName",
                'use_path_style_endpoint' => false,
                'use_single_bucket' => true,
                'visibility' => $isPublic ? 'public' : 'private',
                'throw' => false,
            ];
        } else {
            $disk += [
                'driver' => 's3',
                'key' => env('DO_SPACES_KEY'),
                'secret' => env('DO_SPACES_SECRET'),
                'region' => $doRegion,
                'bucket' => $bucketName,
                'endpoint' => "https://$doRegion.digitaloceanspaces.com",
                'url' => "https://$bucketName.$doRegion.digitaloceanspaces.com",
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
