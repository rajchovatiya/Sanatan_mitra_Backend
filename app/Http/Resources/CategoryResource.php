<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Support\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Category */
class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->tr('name'),
            'icon_url' => Media::url($this->icon_path),
        ];
    }
}
