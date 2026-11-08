<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TempleResource;
use App\Models\Darshan;
use App\Models\Temple;
use App\Support\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class TempleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $q = $request->validate([
            'type' => ['nullable', Rule::in(array_keys(Temple::TYPES))],
            'state' => ['nullable', 'string', 'max:60'],
        ]);

        $temples = Temple::visible()
            ->when($q['type'] ?? null, fn ($w, $type) => $w->where('type', $type))
            ->when($q['state'] ?? null, fn ($w, $state) => $w->where('state', $state))
            ->select(['id', 'type', 'name', 'location', 'state', 'deity', 'image_path'])
            ->paginate(30);

        return TempleResource::collection($temples);
    }

    public function show(int $temple): TempleResource
    {
        return new TempleResource(Temple::where('is_active', true)->findOrFail($temple));
    }

    /** The latest darshan up to today; data is null when none has been posted. */
    public function darshanToday(): JsonResponse
    {
        $d = Darshan::where('is_active', true)
            ->where('date', '<=', today())
            ->latest('date')->latest('id')
            ->first();

        return response()->json(['data' => $d ? [
            'id' => $d->id,
            'date' => $d->date->format('Y-m-d'),
            'title' => $d->tr('title'),
            'image_url' => Media::url($d->image_path),
            'video_url' => $d->video_url,
            'temple_id' => $d->temple_id,
        ] : null]);
    }
}
