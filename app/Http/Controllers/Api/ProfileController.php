<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Requests\Api\UploadImageRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\Media;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function update(UpdateProfileRequest $request): UserResource
    {
        $user = $request->user();
        $user->update($request->validated());

        return new UserResource($user);
    }

    public function updatePhoto(UploadImageRequest $request): UserResource
    {
        return $this->replaceImage($request, 'photo_path', 'profile-photos');
    }

    public function updateBusinessLogo(UploadImageRequest $request): UserResource
    {
        return $this->replaceImage($request, 'business_logo_path', 'business-logos');
    }

    /** Stores the new image under a random name and removes the old one. */
    private function replaceImage(UploadImageRequest $request, string $column, string $folder): UserResource
    {
        /** @var User $user */
        $user = $request->user();
        $user->forceFill([$column => Media::replace($request->file('image'), $user->{$column}, $folder)])->save();

        return new UserResource($user);
    }
}
