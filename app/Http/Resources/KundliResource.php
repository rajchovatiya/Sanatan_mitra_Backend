<?php

namespace App\Http\Resources;

use App\Models\Kundli;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Kundli */
class KundliResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'relation' => $this->relation,
            'gender' => $this->gender,
            'birth_date' => $this->birth_date->format('Y-m-d'),
            'birth_time' => substr($this->birth_time, 0, 5),
            'birth_place' => $this->birth_place,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'tz_offset' => $this->tz_offset,
        ];
    }
}
