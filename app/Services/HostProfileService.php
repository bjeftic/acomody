<?php

namespace App\Services;

use App\Models\HostProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class HostProfileService
{
    public function __construct(
        private readonly ImageUploadService $imageUploadService
    ) {}

    public function createHostProfile(User $user, array $data): HostProfile
    {
        return HostProfile::create(array_merge($data, ['user_id' => $user->id]));
    }

    public function updateHostProfile(User $user, array $data): HostProfile
    {
        /** @var HostProfile $hostProfile */
        $hostProfile = $user->hostProfile;
        $hostProfile->update($data);

        /** @var HostProfile $fresh */
        $fresh = $hostProfile->fresh();

        return $fresh;
    }

    public function uploadAvatar(User $user, UploadedFile $file): HostProfile
    {
        $preset = config('images.presets.user_profile');

        $this->imageUploadService->setDisk($preset['disk']);
        $this->imageUploadService->setSizes($preset['sizes']);

        /** @var HostProfile $hostProfile */
        $hostProfile = $user->hostProfile;
        $oldAvatar = $hostProfile->avatar;

        $result = $this->imageUploadService->upload($file, "hosts/{$user->id}");

        $mediumPath = $result['resized_paths']['medium'];

        $hostProfile->update(['avatar' => $mediumPath]);

        if ($oldAvatar) {
            Storage::disk($preset['disk'])->delete($oldAvatar);
        }

        /** @var HostProfile $fresh */
        $fresh = $hostProfile->fresh();

        return $fresh;
    }
}
