<?php

namespace App\Jobs;

use App\Services\ImageUploadService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncGoogleAvatarJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly int $userId,
        public readonly string $avatarUrl,
    ) {}

    public function handle(ImageUploadService $imageUploadService): void
    {
        // Use DB directly to avoid Eloquent authorization checks in job context (no auth user)
        $profile = DB::table('user_profiles')->where('user_id', $this->userId)->first();

        if (! $profile) {
            return;
        }

        if ($profile->avatar) {
            return;
        }

        try {
            $response = Http::timeout(15)->get($this->avatarUrl);

            if (! $response->successful()) {
                Log::warning('SyncGoogleAvatarJob: failed to download avatar', [
                    'user_id' => $this->userId,
                    'url' => $this->avatarUrl,
                    'status' => $response->status(),
                ]);

                return;
            }

            $preset = config('images.presets.user_profile');
            $imageUploadService->setDisk($preset['disk']);
            $imageUploadService->setSizes($preset['sizes']);

            $tempPath = tempnam(sys_get_temp_dir(), 'google_avatar_');
            file_put_contents($tempPath, $response->body());

            $file = new UploadedFile($tempPath, 'avatar.jpg', 'image/jpeg', null, true);
            $result = $imageUploadService->upload($file, "users/{$this->userId}");

            @unlink($tempPath);

            DB::table('user_profiles')
                ->where('user_id', $this->userId)
                ->update(['avatar' => $result['resized_paths']['medium']]);

            Log::info('SyncGoogleAvatarJob: avatar synced', ['user_id' => $this->userId]);
        } catch (Throwable $e) {
            Log::error('SyncGoogleAvatarJob: failed', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
