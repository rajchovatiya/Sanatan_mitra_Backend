<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PosterRequest;
use App\Models\Category;
use App\Models\Poster;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosterController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->integer('category') ?: null;

        $posters = Poster::with('category:id,name')
            ->when($category, fn ($q) => $q->where('category_id', $category))
            ->latest('id')
            ->paginate(24)->withQueryString();

        return view('admin.posters.index', ['posters' => $posters, 'category' => $category, 'categories' => $this->categories()]);
    }

    public function create(): View
    {
        return $this->form(new Poster(['is_active' => true, 'frame' => 'classic']));
    }

    public function store(PosterRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $data['image_path'] = Media::store($request->file('image'), 'posters');
        Poster::create($data);

        return redirect()->route('admin.posters.index')->with('status', 'Poster added.');
    }

    public function edit(Poster $poster): View
    {
        return $this->form($poster);
    }

    public function update(PosterRequest $request, Poster $poster): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $data['image_path'] = Media::replace($request->file('image'), $poster->image_path, 'posters');
        $poster->update($data);

        return redirect()->route('admin.posters.index')->with('status', 'Poster saved.');
    }

    public function destroy(Poster $poster): RedirectResponse
    {
        $poster->delete();
        Media::delete($poster->image_path);

        return back()->with('status', 'Poster deleted.');
    }

    private function form(Poster $poster): View
    {
        return view('admin.posters.form', ['poster' => $poster, 'categories' => $this->categories()]);
    }

    private function categories()
    {
        return Category::ofType('poster')->orderBy('sort_order')->get(['id', 'name']);
    }
}
