<?php

namespace App\Models;

use App\Http\Resources\FestivalResource;
use App\Http\Resources\MantraResource;
use App\Http\Resources\TempleResource;
use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'type', 'item_id'])]
class Favorite extends Model
{
    public const UPDATED_AT = null;

    public const MAX_PER_USER = 500;

    /** Favourite type => model. Only visible (active) items can be saved. */
    public const TYPES = [
        'mantra' => Mantra::class,
        'library' => LibraryItem::class,
        'temple' => Temple::class,
        'festival' => Festival::class,
        'poster' => Poster::class,
        'wallpaper' => StudioMedia::class,
    ];

    /** Short card data for a saved item, so the favourites screen needs no extra calls. */
    public static function summary(string $type, Model $item): array
    {
        return match ($type) {
            'mantra' => (new MantraResource($item))->resolve(),
            'temple' => (new TempleResource($item))->resolve(),
            'festival' => (new FestivalResource($item))->resolve(),
            'library' => ['id' => $item->id, 'type' => $item->type, 'title' => $item->tr('title'), 'image_url' => Media::url($item->image_path)],
            'poster', 'wallpaper' => ['id' => $item->id, 'title' => $item->tr('title'), 'image_url' => Media::url($item->image_path)],
        };
    }
}
