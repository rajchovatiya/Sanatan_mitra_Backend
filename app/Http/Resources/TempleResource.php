<?php

namespace App\Http\Resources;

use App\Models\Temple;
use App\Support\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Temple Long text and map position only on the detail endpoint. */
class TempleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $detail = array_key_exists('about', $this->resource->getAttributes());

        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->tr('name'),
            'location' => $this->tr('location'),
            'state' => $this->state,
            'deity' => $this->tr('deity'),
            'image_url' => Media::url($this->image_path),
            $this->mergeWhen($detail, fn () => [
                'about' => $this->tr('about'),
                'timings' => $this->tr('timings'),
                'best_time' => $this->tr('best_time'),
                'lat' => $this->lat,
                'lng' => $this->lng,
            ]),
        ];
    }
}
