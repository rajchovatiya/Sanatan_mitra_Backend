<?php

namespace App\Http\Resources;

use App\Models\Rashifal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Rashifal */
class RashifalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $texts = [];
        foreach (Rashifal::AREAS as $area) {
            $texts[$area] = $this->tr($area);
        }

        return [
            'rashi' => $this->rashi,
            'date' => $this->date->format('Y-m-d'),
            'texts' => $texts,
            'stars' => $this->stars ?? (object) [],
            'lucky_number' => $this->lucky_number,
            'lucky_colour' => $this->tr('lucky_colour'),
            'lucky_time' => $this->lucky_time,
            'lucky_direction' => $this->tr('lucky_direction'),
        ];
    }
}
