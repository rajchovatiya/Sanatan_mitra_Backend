<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FestivalResource;
use App\Http\Resources\MantraResource;
use App\Http\Resources\TempleResource;
use App\Models\Feedback;
use App\Models\Festival;
use App\Models\LibraryItem;
use App\Models\Mantra;
use App\Models\Page;
use App\Models\Temple;
use App\Support\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/** Search, feedback and fixed pages. */
class SupportController extends Controller
{
    private const PER_SECTION = 10;

    /** Title search across mantras, temples, library and upcoming festivals, in any language. */
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->validate(['q' => ['required', 'string', 'min:2', 'max:50']])['q']);
        $like = '%'.addcslashes(mb_strtolower($q), '%_\\').'%';
        $match = fn (Builder $b, string $column) => $b->whereRaw("LOWER(CAST({$column} AS CHAR)) LIKE ?", [$like]);

        return response()->json(['data' => [
            'mantras' => MantraResource::collection($match(Mantra::visible(), 'title')->limit(self::PER_SECTION)
                ->get(['id', 'category_id', 'title', 'deity', 'image_path', 'audio_path', 'duration_seconds']))->resolve($request),
            'temples' => TempleResource::collection($match(Temple::visible(), 'name')->limit(self::PER_SECTION)
                ->get(['id', 'type', 'name', 'location', 'state', 'deity', 'image_path']))->resolve($request),
            'library' => $match(LibraryItem::visible(), 'title')->limit(self::PER_SECTION)->get(['id', 'type', 'title', 'image_path'])
                ->map(fn ($i) => ['id' => $i->id, 'type' => $i->type, 'title' => $i->tr('title'), 'image_url' => Media::url($i->image_path)])->all(),
            'festivals' => FestivalResource::collection($match(Festival::visible(), 'name')->where('date', '>=', today()->toDateString())
                ->limit(self::PER_SECTION)->get(['id', 'is_vrat', 'name', 'tithi', 'date', 'image_path']))->resolve($request),
        ]]);
    }

    /** Works with or without login; a logged-in user is linked to the message. */
    public function feedback(Request $request): Response
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:5', 'max:2000'],
            'contact' => ['nullable', 'string', 'max:100'],
            'platform' => ['nullable', 'in:android,ios'],
            'app_version' => ['nullable', 'string', 'max:20'],
        ]);

        Feedback::create($data + ['user_id' => auth('sanctum')->id()]);

        return response()->noContent();
    }

    public function page(string $slug): JsonResponse
    {
        $page = Page::findOrFail($slug);

        return response()->json(['data' => ['slug' => $page->slug, 'title' => $page->tr('title'), 'body' => $page->tr('body')]]);
    }
}
