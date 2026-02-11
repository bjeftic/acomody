<?php

namespace Database\Factories;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Photo>
 */
class PhotoFactory extends Factory
{
    protected $model = Photo::class;

    public function definition(): array
    {
        $width = fake()->randomElement([1920, 1600, 1440, 1280, 800]);
        $height = fake()->randomElement([1080, 900, 960, 720, 600]);
        $fileSize = fake()->numberBetween(100000, 5000000); // 100KB - 5MB

        $ulid = Str::ulid()->toBase32();

        // Default je draft, ali će biti overridden u forAccommodation()
        $folderPrefix = 'draft-' . Str::ulid()->toBase32();

        // Generate date_taken safely
        $dateTaken = fake()->optional(0.7)->dateTime();
        $dateTakenFormatted = $dateTaken ? $dateTaken->format('Y-m-d H:i:s') : null;

        return [
            'photoable_type' => null,
            'photoable_id' => null,
            'disk' => 'accommodation_draft_photos',

            // Paths - will be overridden by state methods
            'path' => "{$folderPrefix}/original/{$ulid}.jpg",
            'thumbnail_path' => "{$folderPrefix}/thumbnail/{$ulid}.jpg",
            'medium_path' => "{$folderPrefix}/medium/{$ulid}.jpg",
            'large_path' => "{$folderPrefix}/large/{$ulid}.jpg",

            // File information
            'original_filename' => fake()->word() . '-' . fake()->word() . '.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => $fileSize,
            'width' => $width,
            'height' => $height,

            // Ordering and status
            'order' => 0,
            'is_primary' => false,
            'status' => 'completed',

            // Metadata
            'metadata' => [
                'width' => $width,
                'height' => $height,
                'mime_type' => 'image/jpeg',
                'file_size' => $fileSize,
                'aspect_ratio' => round($width / $height, 2),
                'exif' => [
                    'orientation' => null,
                    'camera_make' => fake()->optional(0.3)->randomElement(['Canon', 'Nikon', 'Sony']),
                    'camera_model' => fake()->optional(0.3)->randomElement(['EOS 80D', 'D750', 'A7III']),
                    'date_taken' => $dateTakenFormatted,
                    'exposure_time' => null,
                    'f_number' => null,
                    'iso' => fake()->optional(0.4)->randomElement([100, 200, 400, 800, 1600]),
                ],
            ],

            'alt_text' => fake()->optional(0.3)->sentence(),
            'caption' => fake()->optional(0.2)->sentence(),

            // Timestamps
            'uploaded_at' => now(),
            'processed_at' => now(),
            'error_message' => null,
        ];
    }

    /**
     * Make this photo primary
     */
    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
            'order' => 0,
        ]);
    }

    /**
     * Set as failed status
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error_message' => fake()->sentence(),
            'processed_at' => null,
        ]);
    }

    /**
     * Set as pending status
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'processed_at' => null,
        ]);
    }

    /**
     * Set as processing status
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
            'processed_at' => null,
        ]);
    }

    /**
     * Set specific order
     */
    public function order(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order' => $order,
        ]);
    }

    /**
     * Use accommodation photos disk with proper property- prefix
     */
    public function forAccommodation(): static
    {
        return $this->state(function (array $attributes) {
            $ulid = Str::ulid()->toBase32();
            $folderPrefix = 'property-' . Str::ulid()->toBase32(); // <-- property prefix

            return [
                'disk' => 'accommodation_photos',
                'path' => "{$folderPrefix}/original/{$ulid}.jpg",
                'thumbnail_path' => "{$folderPrefix}/thumbnail/{$ulid}.jpg",
                'medium_path' => "{$folderPrefix}/medium/{$ulid}.jpg",
                'large_path' => "{$folderPrefix}/large/{$ulid}.jpg",
            ];
        });
    }

    /**
     * Use draft photos disk with proper draft- prefix (explicit method)
     */
    public function forDraft(): static
    {
        return $this->state(function (array $attributes) {
            $ulid = Str::ulid()->toBase32();
            $folderPrefix = 'draft-' . Str::ulid()->toBase32(); // <-- draft prefix

            return [
                'disk' => 'accommodation_draft_photos',
                'path' => "{$folderPrefix}/original/{$ulid}.jpg",
                'thumbnail_path' => "{$folderPrefix}/thumbnail/{$ulid}.jpg",
                'medium_path' => "{$folderPrefix}/medium/{$ulid}.jpg",
                'large_path' => "{$folderPrefix}/large/{$ulid}.jpg",
            ];
        });
    }

    /**
     * Set specific dimensions
     */
    public function dimensions(int $width, int $height): static
    {
        return $this->state(function (array $attributes) use ($width, $height) {
            $metadata = $attributes['metadata'];
            $metadata['width'] = $width;
            $metadata['height'] = $height;
            $metadata['aspect_ratio'] = round($width / $height, 2);

            return [
                'width' => $width,
                'height' => $height,
                'metadata' => $metadata,
            ];
        });
    }

    /**
     * Generate with picsum.photos URLs (for testing/demo)
     */
    public function withPicsumPaths(): static
    {
        return $this->state(function (array $attributes) {
            $width = $attributes['width'];
            $height = $attributes['height'];
            $seed = fake()->numberBetween(1, 1000);

            return [
                'path' => "seed/{$seed}/{$width}/{$height}.jpg",
                'thumbnail_path' => "seed/{$seed}/300/200.jpg",
                'medium_path' => "seed/{$seed}/800/600.jpg",
                'large_path' => "seed/{$seed}/1200/800.jpg",
            ];
        });
    }
}
