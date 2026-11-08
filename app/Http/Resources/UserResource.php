<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mobile' => $this->mobile,
            'name' => $this->name,
            'gender' => $this->gender,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'birth_time' => $this->birth_time ? substr($this->birth_time, 0, 5) : null,
            'birth_place' => $this->birth_place,
            'birth_lat' => $this->birth_lat,
            'birth_lng' => $this->birth_lng,
            'city' => $this->city,
            'rashi' => $this->rashi,
            'language' => $this->language,
            'notifications_enabled' => $this->notifications_enabled,
            'photo_url' => $this->fileUrl($this->photo_path),
            'business_type' => $this->business_type,
            'business_name' => $this->business_name,
            'business_address' => $this->business_address,
            'business_logo_url' => $this->fileUrl($this->business_logo_path),
            'other_details' => $this->other_details,
        ];
    }

    private function fileUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }
}
