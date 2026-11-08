<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MantraRequest;
use App\Models\Category;
use App\Models\Mantra;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MantraController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->integer('category') ?: null;

        $mantras = Mantra::with('category:id,name')
            ->when($category, fn ($q) => $q->where('category_id', $category))
            ->select(['id', 'category_id', 'title', 'deity', 'image_path', 'audio_path', 'is_active'])
            ->latest('id')
            ->paginate(30)->withQueryString();

        return view('admin.mantras.index', ['mantras' => $mantras, 'category' => $category, 'categories' => $this->categories()]);
    }

    public function create(): View
    {
        return $this->form(new Mantra(['is_active' => true]));
    }

    public function store(MantraRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'audio']);
        $data['image_path'] = $request->hasFile('image') ? Media::store($request->file('image'), 'mantra-images') : null;
        $data['audio_path'] = $request->hasFile('audio') ? Media::store($request->file('audio'), 'mantra-audio') : null;
        Mantra::create($data);

        return redirect()->route('admin.mantras.index')->with('status', 'Mantra added.');
    }

    public function edit(Mantra $mantra): View
    {
        return $this->form($mantra);
    }

    public function update(MantraRequest $request, Mantra $mantra): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'audio']);
        $data['image_path'] = Media::replace($request->file('image'), $mantra->image_path, 'mantra-images');
        $data['audio_path'] = Media::replace($request->file('audio'), $mantra->audio_path, 'mantra-audio');
        $mantra->update($data);

        return redirect()->route('admin.mantras.index')->with('status', 'Mantra saved.');
    }

    public function destroy(Mantra $mantra): RedirectResponse
    {
        $mantra->delete();
        Media::delete($mantra->image_path, $mantra->audio_path);

        return back()->with('status', 'Mantra deleted.');
    }

    private function form(Mantra $mantra): View
    {
        return view('admin.mantras.form', ['mantra' => $mantra, 'categories' => $this->categories()]);
    }

    private function categories()
    {
        return Category::ofType('mantra')->orderBy('sort_order')->get(['id', 'name']);
    }
}
