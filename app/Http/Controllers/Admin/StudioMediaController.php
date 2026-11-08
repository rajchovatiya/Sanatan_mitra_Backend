<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudioMediaRequest;
use App\Models\Category;
use App\Models\StudioMedia;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Wallpapers and video templates. */
class StudioMediaController extends Controller
{
    public function index(Request $request): View
    {
        $kind = $request->validate(['kind' => ['nullable', Rule::in(array_keys(StudioMedia::KINDS))]])['kind'] ?? 'wallpaper';

        $items = StudioMedia::with('category:id,name')->where('kind', $kind)
            ->latest('id')->paginate(24)->withQueryString();

        return view('admin.studio.index', compact('items', 'kind'));
    }

    public function create(Request $request): View
    {
        return $this->form(new StudioMedia(['kind' => $request->query('kind', 'wallpaper')]));
    }

    public function store(StudioMediaRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'video']);
        $data['image_path'] = Media::store($request->file('image'), "studio-{$data['kind']}");
        $data['video_path'] = $request->hasFile('video') ? Media::store($request->file('video'), 'studio-video-files') : null;
        StudioMedia::create($data);

        return redirect()->route('admin.studio.index', ['kind' => $data['kind']])->with('status', 'Uploaded.');
    }

    public function edit(StudioMedia $studio): View
    {
        return $this->form($studio);
    }

    public function update(StudioMediaRequest $request, StudioMedia $studio): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'video']);
        $data['image_path'] = Media::replace($request->file('image'), $studio->image_path, "studio-{$studio->kind}");
        $data['video_path'] = Media::replace($request->file('video'), $studio->video_path, 'studio-video-files');
        $studio->update($data);

        return redirect()->route('admin.studio.index', ['kind' => $studio->kind])->with('status', 'Saved.');
    }

    public function destroy(StudioMedia $studio): RedirectResponse
    {
        $studio->delete();
        Media::delete($studio->image_path, $studio->video_path);

        return back()->with('status', 'Deleted.');
    }

    private function form(StudioMedia $item): View
    {
        return view('admin.studio.form', [
            'item' => $item,
            'categories' => Category::ofType($item->kind)->orderBy('sort_order')->get(['id', 'name']),
        ]);
    }
}
