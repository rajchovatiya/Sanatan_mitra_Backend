<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FestivalResource;
use App\Http\Resources\MantraResource;
use App\Http\Resources\RashifalResource;
use App\Http\Resources\TempleResource;
use App\Models\Darshan;
use App\Models\Festival;
use App\Models\GitaVerse;
use App\Models\Mantra;
use App\Models\Rashifal;
use App\Models\Suvichar;
use App\Models\Temple;
use App\Support\DailyPick;
use App\Support\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Everything the Home screen shows, in one request. Cached for 10 minutes per day and
 * language, so admin edits appear within 10 minutes and most requests are a single cache read.
 */
class HomeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $today = today();
        $key = 'home:'.$today->toDateString().':'.app()->getLocale();

        $data = Cache::remember($key, now()->addMinutes(10), function () use ($request, $today) {
            $darshan = Darshan::where('is_active', true)->where('date', '<=', $today)->latest('date')->latest('id')->first();
            $suvichar = Suvichar::where('is_active', true)->whereDate('date', $today)->latest('id')->first()
                ?? DailyPick::from(Suvichar::where('is_active', true)->whereNull('date'), $today);

            return [
                'rashifal' => RashifalResource::collection(
                    Rashifal::whereDate('date', $today)->orderBy('rashi')->get()
                )->resolve($request),
                'festivals' => FestivalResource::collection(
                    Festival::visible()->where('date', '>=', $today->toDateString())->limit(5)
                        ->get(['id', 'is_vrat', 'name', 'tithi', 'date', 'image_path'])
                )->resolve($request),
                'suvichar' => $suvichar ? ['text' => $suvichar->tr('text'), 'author' => $suvichar->tr('author')] : null,
                'gita_verse' => DailyPick::from(GitaVerse::where('is_featured', true), $today)?->toApi(),
                'darshan' => $darshan ? [
                    'id' => $darshan->id, 'title' => $darshan->tr('title'),
                    'image_url' => Media::url($darshan->image_path), 'video_url' => $darshan->video_url,
                ] : null,
                'mantras' => MantraResource::collection(
                    Mantra::visible()->limit(8)
                        ->get(['id', 'category_id', 'title', 'deity', 'image_path', 'audio_path', 'duration_seconds'])
                )->resolve($request),
                'temples' => TempleResource::collection(
                    Temple::visible()->limit(10)->get(['id', 'type', 'name', 'location', 'state', 'deity', 'image_path'])
                )->resolve($request),
            ];
        });

        return response()->json(['data' => $data]);
    }
}
