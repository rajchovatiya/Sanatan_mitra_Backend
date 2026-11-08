<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\MantraResource;
use App\Http\Resources\PosterResource;
use App\Models\Category;
use App\Models\Mantra;
use App\Models\Poster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

/** Read-only content for the app: categories, posters and mantras. */
class ContentController extends Controller
{
    private const PER_PAGE = 30;

    /** Cached per type and language; cleared when the admin edits a category. */
    public function categories(Request $request, string $type): JsonResponse
    {
        $data = Cache::rememberForever(Category::cacheKey($type, app()->getLocale()), fn () => CategoryResource::collection(
            Category::ofType($type)->visible()->get(['id', 'name', 'icon_path'])
        )->resolve($request));

        return response()->json(['data' => $data]);
    }

    public function posters(Request $request): AnonymousResourceCollection
    {
        $request->validate(['category' => ['nullable', 'integer']]);

        $posters = Poster::visible()
            ->when($request->integer('category'), fn ($q, $id) => $q->where('category_id', $id))
            ->select(['id', 'category_id', 'title', 'image_path', 'frame'])
            ->paginate(self::PER_PAGE);

        return PosterResource::collection($posters);
    }

    public function mantras(Request $request): AnonymousResourceCollection
    {
        $request->validate(['category' => ['nullable', 'integer']]);

        $mantras = Mantra::visible()
            ->when($request->integer('category'), fn ($q, $id) => $q->where('category_id', $id))
            ->select(['id', 'category_id', 'title', 'deity', 'image_path', 'audio_path', 'duration_seconds'])
            ->paginate(self::PER_PAGE);

        return MantraResource::collection($mantras);
    }

    public function mantra(int $mantra): MantraResource
    {
        return new MantraResource(Mantra::where('is_active', true)->findOrFail($mantra));
    }
}
