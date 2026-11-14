<?php

namespace App\Http\Resources;

use App\Models\Poster;
use App\Support\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Poster */
class PosterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'title' => $this->tr('title'),
            'image_url' => Media::url($this->image_path),
            'frame' => $this->frame,
            'footer_below' => (bool) $this->footer_below,
        ];
    }
}
