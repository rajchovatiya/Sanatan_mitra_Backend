<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\LearningVideo;
use App\Models\StudioMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/** Studio wallpapers and video templates, live streams and courses. */
class MediaController extends Controller
{
    /** kind comes from the route: wallpaper or video. */
    public function studio(Request $request, string $kind): AnonymousResourceCollection
    {
        $request->validate(['category' => ['nullable', 'integer']]);

        $page = StudioMedia::visible()->where('kind', $kind)
            ->when($request->integer('category'), fn ($q, $id) => $q->where('category_id', $id))
            ->select(['id', 'category_id', 'title', 'image_path', 'video_path'])
            ->paginate(30);

        return JsonResource::collection($page->through(fn (StudioMedia $m) => $m->toApi()));
    }

    public function live(): JsonResponse
    {
        $videos = LearningVideo::visible()->where('kind', 'live')->limit(50)->get();

        return response()->json(['data' => $videos->map->toApi()->all()]);
    }

    /** A course with its lessons (courses themselves come from /courses, the category list). */
    public function course(int $course): JsonResponse
    {
        $c = Category::ofType('course')->where('is_active', true)->findOrFail($course);
        $lessons = $c->learningVideos()->where('kind', 'lesson')->visible()->limit(200)->get();

        return response()->json(['data' => [
            'id' => $c->id,
            'name' => $c->tr('name'),
            'description' => $c->tr('description'),
            'lessons' => $lessons->map->toApi()->all(),
        ]]);
    }
}
