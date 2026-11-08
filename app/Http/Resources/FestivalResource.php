<?php

namespace App\Http\Resources;

use App\Models\Festival;
use App\Support\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Festival About and rituals only on the detail endpoint. */
class FestivalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $detail = array_key_exists('about', $this->resource->getAttributes());

        return [
            'id' => $this->id,
            'name' => $this->tr('name'),
            'tithi' => $this->tr('tithi'),
            'date' => $this->date->format('Y-m-d'),
            'is_vrat' => $this->is_vrat,
            'image_url' => Media::url($this->image_path),
            'about' => $this->when($detail, fn () => $this->tr('about')),
            'rituals' => $this->when($detail, fn () => $this->ritualSteps()),
        ];
    }
}
