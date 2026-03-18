<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Http\Requests\Host\StoreHostProfileRequest;
use App\Http\Requests\Host\UpdateHostProfileRequest;
use App\Http\Requests\User\AvatarUploadRequest;
use App\Http\Resources\HostProfileResource;
use App\Http\Support\ApiResponse;
use App\Services\HostProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HostProfileController extends Controller
{
    public function __construct(private readonly HostProfileService $hostProfileService) {}

    public function show(): JsonResponse
    {
        $user = Auth::user();

        if (! $user->hostProfile) {
            return ApiResponse::success('No host profile found.', null);
        }

        return ApiResponse::success(
            'Host profile retrieved successfully.',
            new HostProfileResource($user->hostProfile->load('country'))
        );
    }

    public function initialize(): JsonResponse
    {
        $user = Auth::user();

        $hostProfile = $user->hostProfile ?? $this->hostProfileService->createHostProfile($user, []);

        return ApiResponse::success(
            'Host profile initialized.',
            new HostProfileResource($hostProfile->load('country')),
        );
    }

    public function store(StoreHostProfileRequest $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->hostProfile) {
            throw new HttpException(409, 'Host profile already exists.');
        }

        $hostProfile = $this->hostProfileService->createHostProfile($user, $request->validated());

        return ApiResponse::success(
            'Host profile created successfully.',
            new HostProfileResource($hostProfile->load('country')),
        );
    }

    public function update(UpdateHostProfileRequest $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user->hostProfile) {
            throw new HttpException(404, 'Host profile not found.');
        }

        $hostProfile = $this->hostProfileService->updateHostProfile($user, $request->validated());

        return ApiResponse::success(
            'Host profile updated successfully.',
            new HostProfileResource($hostProfile->load('country'))
        );
    }

    public function uploadAvatar(AvatarUploadRequest $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user->hostProfile) {
            throw new HttpException(404, 'Host profile not found.');
        }

        $hostProfile = $this->hostProfileService->uploadAvatar($user, $request->file('avatar'));

        return ApiResponse::success(
            'Host profile avatar updated successfully.',
            new HostProfileResource($hostProfile->load('country'))
        );
    }
}
