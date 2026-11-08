<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TempleRequest;
use App\Models\Temple;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TempleController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->validate(['type' => ['nullable', Rule::in(array_keys(Temple::TYPES))]])['type'] ?? null;

        $temples = Temple::query()
            ->when($type, fn ($q) => $q->where('type', $type))
            ->select(['id', 'type', 'name', 'state', 'image_path', 'sort_order', 'is_active'])
            ->orderBy('type')->orderBy('sort_order')->orderBy('id')
            ->paginate(30)->withQueryString();

        return view('admin.temples.index', compact('temples', 'type'));
    }

    public function create(): View
    {
        return view('admin.temples.form', ['temple' => new Temple]);
    }

    public function store(TempleRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $data['image_path'] = $request->hasFile('image') ? Media::store($request->file('image'), 'temples') : null;
        Temple::create($data);

        return redirect()->route('admin.temples.index')->with('status', 'Temple added.');
    }

    public function edit(Temple $temple): View
    {
        return view('admin.temples.form', compact('temple'));
    }

    public function update(TempleRequest $request, Temple $temple): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $data['image_path'] = Media::replace($request->file('image'), $temple->image_path, 'temples');
        $temple->update($data);

        return redirect()->route('admin.temples.index')->with('status', 'Temple saved.');
    }

    public function destroy(Temple $temple): RedirectResponse
    {
        $temple->delete();
        Media::delete($temple->image_path);

        return back()->with('status', 'Temple deleted.');
    }
}
