<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Image Upload Settings
    |--------------------------------------------------------------------------
    */
    'max_file_size' => env('IMAGE_MAX_FILE_SIZE', 10485760), // 10MB

    'allowed_mime_types' => [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Image Sizes
    |--------------------------------------------------------------------------
    */
    'sizes' => [
        'thumbnail' => [
            'width' => 300,
            'height' => 300,
            'quality' => 80,
            'crop' => true, // Cover (crop) instead of scale
        ],
        'medium' => [
            'width' => 800,
            'height' => 600,
            'quality' => 85,
        ],
        'large' => [
            'width' => 1920,
            'height' => 1440,
            'quality' => 90,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Presets for Different Entities
    |--------------------------------------------------------------------------
    |
    | Define custom configurations for different upload types
    |
    */
    'presets' => [
        'accommodation_draft' => [
            'disk' => 'accommodation_draft_photos',
            'max_file_size' => 10485760,
            'sizes' => [
                'thumbnail' => ['width' => 300, 'height' => 300, 'quality' => 80, 'crop' => true],
                'medium' => ['width' => 800, 'height' => 600, 'quality' => 85],
                'large' => ['width' => 1920, 'height' => 1440, 'quality' => 90],
            ],
        ],

        'accommodation' => [
            'disk' => 'accommodation_photos',
            'max_file_size' => 10485760,
            'sizes' => [
                'thumbnail' => ['width' => 300, 'height' => 300, 'quality' => 80, 'crop' => true],
                'medium' => ['width' => 800, 'height' => 600, 'quality' => 85],
                'large' => ['width' => 1920, 'height' => 1440, 'quality' => 90],
            ],
        ],

        // User Profile Photos
        'user_profile' => [
            'disk' => 'user_profile_photos',
            'base_path' => 'users',
            'max_file_size' => 5242880, // 5MB
            'sizes' => [
                'thumbnail' => ['width' => 100, 'height' => 100, 'quality' => 80, 'crop' => true],
                'small' => ['width' => 200, 'height' => 200, 'quality' => 85, 'crop' => true],
                'medium' => ['width' => 400, 'height' => 400, 'quality' => 90, 'crop' => true],
            ],
        ],

        // User Cover Photos
        'user_cover' => [
            'disk' => env('APP_ENV') === 'production' ? 's3' : 'minio',
            'base_path' => 'users/covers',
            'max_file_size' => 5242880,
            'sizes' => [
                'small' => ['width' => 800, 'height' => 200, 'quality' => 80, 'crop' => true],
                'medium' => ['width' => 1200, 'height' => 300, 'quality' => 85, 'crop' => true],
                'large' => ['width' => 1920, 'height' => 480, 'quality' => 90, 'crop' => true],
            ],
        ],

        // Blog Post Images
        'blog_post' => [
            'disk' => env('APP_ENV') === 'production' ? 's3' : 'minio',
            'base_path' => 'blog',
            'max_file_size' => 5242880,
            'sizes' => [
                'thumbnail' => ['width' => 400, 'height' => 300, 'quality' => 80],
                'medium' => ['width' => 800, 'height' => 600, 'quality' => 85],
                'large' => ['width' => 1200, 'height' => 900, 'quality' => 90],
            ],
        ],

        // Category/Destination Images
        'category' => [
            'disk' => env('APP_ENV') === 'production' ? 's3' : 'minio',
            'base_path' => 'categories',
            'max_file_size' => 5242880,
            'sizes' => [
                'thumbnail' => ['width' => 300, 'height' => 200, 'quality' => 80, 'crop' => true],
                'medium' => ['width' => 600, 'height' => 400, 'quality' => 85, 'crop' => true],
                'large' => ['width' => 1200, 'height' => 800, 'quality' => 90, 'crop' => true],
            ],
        ],

        // Review Images
        'review' => [
            'disk' => env('APP_ENV') === 'production' ? 's3' : 'minio',
            'base_path' => 'reviews',
            'max_file_size' => 5242880,
            'sizes' => [
                'thumbnail' => ['width' => 200, 'height' => 200, 'quality' => 80, 'crop' => true],
                'medium' => ['width' => 600, 'height' => 600, 'quality' => 85],
                'large' => ['width' => 1200, 'height' => 1200, 'quality' => 90],
            ],
        ],

        // Documents/PDFs (thumbnails only)
        'document' => [
            'disk' => env('APP_ENV') === 'production' ? 's3' : 'minio',
            'base_path' => 'documents',
            'max_file_size' => 20971520, // 20MB
            'allowed_mime_types' => ['application/pdf', 'image/jpeg', 'image/png'],
            'sizes' => [
                'thumbnail' => ['width' => 200, 'height' => 283, 'quality' => 80], // A4 ratio
            ],
        ],
    ],
];
