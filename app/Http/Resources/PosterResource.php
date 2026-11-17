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
            // Date badge: corner, and a fixed date or null (= the day it is shared).
            'show_date' => (bool) $this->show_date,
            'date_position' => $this->date_position ?? 'top_right',
            'date_on' => $this->date_on?->format('Y-m-d'),
            // Days the app shows this poster first (1 = Monday … 7 = Sunday).
            'weekdays' => array_values(array_map('intval', $this->weekdays ?? [])),
        ];
    }
}
