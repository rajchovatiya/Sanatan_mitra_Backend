<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GitaChapter;
use App\Models\GitaVerse;
use App\Models\LibraryItem;
use App\Models\Suvichar;
use App\Support\DailyPick;
use App\Support\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

/** Katha / Aarti / Beej / Chalisa, Bhagavad Gita and Suvichar. */
class LibraryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $type = $request->validate(['type' => ['nullable', Rule::in(array_keys(LibraryItem::TYPES))]])['type'] ?? null;

        $page = LibraryItem::visible()
            ->when($type, fn ($q) => $q->where('type', $type))
            ->select(['id', 'type', 'title', 'subtitle', 'image_path', 'audio_path'])
            ->paginate(30);

        return JsonResource::collection($page->through(fn (LibraryItem $i) => $this->item($i)));
    }

    public function show(int $item): JsonResponse
    {
        $i = LibraryItem::where('is_active', true)->findOrFail($item);

        return response()->json(['data' => $this->item($i) + ['body' => $i->tr('body')]]);
    }

    /** Cached per language; cleared when a chapter is edited. */
    public function chapters(): JsonResponse
    {
        $data = Cache::rememberForever(GitaChapter::cacheKey(app()->getLocale()), fn () => GitaChapter::orderBy('number')->get()
            ->map(fn (GitaChapter $c) => [
                'number' => $c->number, 'name' => $c->tr('name'), 'summary' => $c->tr('summary'), 'verse_count' => $c->verse_count,
            ])->all());

        return response()->json(['data' => $data]);
    }

    public function verses(int $chapter): AnonymousResourceCollection
    {
        abort_unless($chapter >= 1 && $chapter <= 18, 404);

        $page = GitaVerse::where('chapter', $chapter)->orderBy('verse')->paginate(50);

        return JsonResource::collection($page->through(fn (GitaVerse $v) => $v->toApi()));
    }

    public function verseOfTheDay(): JsonResponse
    {
        $v = DailyPick::from(GitaVerse::where('is_featured', true), today());

        return response()->json(['data' => $v?->toApi()]);
    }

    /** A suvichar set for today, otherwise the daily rotation of undated ones. */
    public function suvichar(): JsonResponse
    {
        $s = Suvichar::where('is_active', true)->whereDate('date', today())->latest('id')->first()
            ?? DailyPick::from(Suvichar::where('is_active', true)->whereNull('date'), today());

        return response()->json(['data' => $s ? ['text' => $s->tr('text'), 'author' => $s->tr('author')] : null]);
    }

    private function item(LibraryItem $i): array
    {
        return [
            'id' => $i->id,
            'type' => $i->type,
            'title' => $i->tr('title'),
            'subtitle' => $i->tr('subtitle'),
            'image_url' => Media::url($i->image_path),
            'audio_url' => Media::url($i->audio_path),
        ];
    }
}
