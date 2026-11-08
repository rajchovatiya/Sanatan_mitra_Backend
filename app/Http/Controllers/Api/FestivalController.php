<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FestivalResource;
use App\Models\Festival;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;

class FestivalController extends Controller
{
    private const LIST_COLUMNS = ['id', 'is_vrat', 'name', 'tithi', 'date', 'image_path'];

    /** One month (this month by default). A month holds a few dozen at most, so no paging. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $q = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'type' => ['nullable', 'in:festival,vrat'],
        ]);
        $start = Carbon::create($q['year'] ?? today()->year, $q['month'] ?? today()->month, 1);

        $festivals = Festival::visible()
            ->whereBetween('date', [$start->toDateString(), $start->copy()->endOfMonth()->toDateTimeString()])
            ->when($q['type'] ?? null, fn ($w, $type) => $w->where('is_vrat', $type === 'vrat'))
            ->limit(100)
            ->get(self::LIST_COLUMNS);

        return FestivalResource::collection($festivals);
    }

    /** The next few from today, for Home. */
    public function upcoming(Request $request): AnonymousResourceCollection
    {
        $limit = $request->validate(['limit' => ['nullable', 'integer', 'between:1,20']])['limit'] ?? 5;

        return FestivalResource::collection(
            Festival::visible()->where('date', '>=', today()->toDateString())->limit($limit)->get(self::LIST_COLUMNS)
        );
    }

    public function show(int $festival): FestivalResource
    {
        return new FestivalResource(Festival::where('is_active', true)->findOrFail($festival));
    }
}
