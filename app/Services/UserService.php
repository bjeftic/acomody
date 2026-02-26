<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(
        protected ImageUploadService $imageUploadService
    ) {}

    public function updateProfile(User $user, array $data): User
    {
        $user->userProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        return $user->load('userProfile');
    }

    public function updatePassword(User $user, array $data): void
    {
        if (!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($data['password']),
            'password_changed_at' => now(),
        ]);
    }

    public function uploadAvatar(User $user, UploadedFile $file): User
    {
        $preset = config('images.presets.user_profile');

        $this->imageUploadService->setDisk($preset['disk']);
        $this->imageUploadService->setSizes($preset['sizes']);

        $oldAvatar = $user->userProfile?->avatar;

        $result = $this->imageUploadService->upload($file, "users/{$user->id}");

        $mediumPath = $result['resized_paths']['medium'];

        $user->userProfile()->updateOrCreate(
            ['user_id' => $user->id],
            ['avatar' => $mediumPath]
        );

        if ($oldAvatar) {
            Storage::disk($preset['disk'])->delete($oldAvatar);
        }

        return $user->load('userProfile');
    }
}
