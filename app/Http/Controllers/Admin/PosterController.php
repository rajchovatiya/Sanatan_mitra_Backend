<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PosterBulkRequest;
use App\Http\Requests\Admin\PosterRequest;
use App\Models\Category;
use App\Models\Poster;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

    /** Many posters at once into one category. */
    public function bulkCreate(): View
    {
        return view('admin.posters.bulk', ['categories' => $this->categories()]);
    }

    public function bulkStore(PosterBulkRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $files = $request->file('images');

        foreach ($files as $i => $file) {
            // The typed English name, or one made from the file name.
            $title = trim((string) ($data['titles'][$i] ?? ''))
                ?: Str::of($file->getClientOriginalName())->beforeLast('.')->replace(['_', '-'], ' ')->squish()->ucfirst()->limit(100, '')->toString();

            Poster::create([
                'category_id' => $data['category_id'],
                'title' => ['en' => $title ?: 'Poster'],
                'image_path' => Media::store($file, 'posters'),
                'frame' => 'classic',
                'footer_below' => $data['footer_below'],
                'is_active' => $data['is_active'],
                'sort_order' => $data['sort_order'] ?? 0,
            ]);
        }

        return redirect()
            ->route('admin.posters.index', ['category' => $data['category_id']])
            ->with('status', count($files).' posters added.');
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
