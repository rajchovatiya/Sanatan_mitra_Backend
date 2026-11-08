<?php

namespace App\Http\Resources;

use App\Models\Mantra;
use App\Support\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Mantra Lyrics and meaning only on the detail endpoint, to keep lists small. */
class MantraResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'title' => $this->tr('title'),
            'deity' => $this->tr('deity'),
            'image_url' => Media::url($this->image_path),
            'audio_url' => Media::url($this->audio_path),
            'duration_seconds' => $this->duration_seconds,
            'lyrics' => $this->when($this->hasText(), fn () => $this->tr('lyrics')),
            'meaning' => $this->when($this->hasText(), fn () => $this->tr('meaning')),
        ];
    }

    /** List queries do not select the long text columns. */
    private function hasText(): bool
    {
        return array_key_exists('lyrics', $this->resource->getAttributes());
    }
}
