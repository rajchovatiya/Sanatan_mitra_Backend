<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Guide;
use App\Models\MuhuratDate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/** Muhurat dates and the reference guides (gems, rudraksha, tarot, numerology, palmistry, graphology). */
class GuideController extends Controller
{
    public function index(string $module): AnonymousResourceCollection
    {
        abort_unless(isset(Guide::MODULES[$module]), 404);

        $page = Guide::visible()->where('module', $module)
            ->select(['id', 'module', 'title', 'subtitle', 'image_path'])
            ->paginate(50);

        return JsonResource::collection($page->through(fn (Guide $g) => $g->toApi(false)));
    }

    public function show(string $module, int $guide): JsonResponse
    {
        $g = Guide::where('module', $module)->where('is_active', true)->findOrFail($guide);

        return response()->json(['data' => $g->toApi(true)]);
    }

    /** Dates for one muhurat type in a year (this year by default); at most one row per day, so no paging. */
    public function muhuratDates(Request $request): JsonResponse
    {
        $q = $request->validate([
            'category' => ['required', 'integer'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);
        $year = $q['year'] ?? today()->year;

        abort_unless(Category::ofType('muhurat')->where('is_active', true)->whereKey($q['category'])->exists(), 404);

        $dates = MuhuratDate::where('category_id', $q['category'])->where('is_active', true)
            ->whereBetween('date', ["{$year}-01-01", "{$year}-12-31 23:59:59"])
            ->orderBy('date')->limit(400)
            ->get(['id', 'date', 'start_time', 'end_time', 'note']);

        return response()->json(['data' => $dates->map->toApi()->all()]);
    }
}
