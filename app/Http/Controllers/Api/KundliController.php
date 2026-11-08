<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\KundliRequest;
use App\Http\Resources\KundliResource;
use App\Models\Kundli;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

/** The user's saved birth profiles. Each user has at most a small fixed number, so no paging. */
class KundliController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $kundlis = $request->user()->kundlis()->latest('id')->limit(config('sanatan.kundlis_per_user'))->get();

        return KundliResource::collection($kundlis);
    }

    public function store(KundliRequest $request): KundliResource
    {
        $limit = config('sanatan.kundlis_per_user');

        if ($request->user()->kundlis()->count() >= $limit) {
            throw ValidationException::withMessages(['name' => "You can save up to {$limit} profiles."]);
        }

        return new KundliResource($request->user()->kundlis()->create($request->validated()));
    }

    public function update(KundliRequest $request, Kundli $kundli): KundliResource
    {
        Gate::authorize('update', $kundli);
        $kundli->update($request->validated());

        return new KundliResource($kundli);
    }

    public function destroy(Kundli $kundli): Response
    {
        Gate::authorize('delete', $kundli);
        $kundli->delete();

        return response()->noContent();
    }
}
