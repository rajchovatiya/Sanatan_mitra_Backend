<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FavoriteController extends Controller
{
    /** Saved items with card data, newest first. Items since turned off are left out. */
    public function index(Request $request): JsonResponse
    {
        $type = $request->validate(['type' => ['nullable', Rule::in(array_keys(Favorite::TYPES))]])['type'] ?? null;

        $favorites = $request->user()->favorites()
            ->when($type, fn ($q) => $q->where('type', $type))
            ->latest('id')->limit(Favorite::MAX_PER_USER)
            ->get(['type', 'item_id', 'created_at']);

        // One query per type, not per item.
        $items = [];
        foreach ($favorites->groupBy('type') as $t => $group) {
            $items[$t] = Favorite::TYPES[$t]::where('is_active', true)->whereIn('id', $group->pluck('item_id'))->get()->keyBy('id');
        }

        $data = $favorites
            ->filter(fn ($f) => isset($items[$f->type][$f->item_id]))
            ->map(fn ($f) => ['type' => $f->type, 'item' => Favorite::summary($f->type, $items[$f->type][$f->item_id])])
            ->values();

        return response()->json(['data' => $data]);
    }

    public function store(Request $request): Response
    {
        $q = $request->validate([
            'type' => ['required', Rule::in(array_keys(Favorite::TYPES))],
            'item_id' => ['required', 'integer'],
        ]);

        $model = Favorite::TYPES[$q['type']];
        if (! $model::where('is_active', true)->whereKey($q['item_id'])->exists()) {
            throw ValidationException::withMessages(['item_id' => 'This item is not available.']);
        }

        $user = $request->user();
        if ($user->favorites()->count() >= Favorite::MAX_PER_USER) {
            throw ValidationException::withMessages(['item_id' => 'You can save up to '.Favorite::MAX_PER_USER.' favourites.']);
        }

        $user->favorites()->firstOrCreate(['type' => $q['type'], 'item_id' => $q['item_id']]);

        return response()->noContent();
    }

    public function destroy(Request $request, string $type, int $itemId): Response
    {
        $request->user()->favorites()->where('type', $type)->where('item_id', $itemId)->delete();

        return response()->noContent();
    }
}
