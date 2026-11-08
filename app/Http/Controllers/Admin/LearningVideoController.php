<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LearningVideoRequest;
use App\Models\Category;
use App\Models\LearningVideo;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Live streams and course lessons. */
class LearningVideoController extends Controller
{
    public function index(Request $request): View
    {
        $kind = $request->validate(['kind' => ['nullable', Rule::in(array_keys(LearningVideo::KINDS))]])['kind'] ?? 'live';

        $videos = LearningVideo::with('category:id,name')->where('kind', $kind)
            ->orderBy('category_id')->orderBy('sort_order')->orderBy('id')
            ->paginate(30)->withQueryString();

        return view('admin.learning.index', compact('videos', 'kind'));
    }

    public function create(Request $request): View
    {
        return $this->form(new LearningVideo(['kind' => $request->query('kind', 'live')]));
    }

    public function store(LearningVideoRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('thumbnail');
        $data['thumbnail_path'] = $request->hasFile('thumbnail') ? Media::store($request->file('thumbnail'), 'video-thumbnails') : null;
        LearningVideo::create($data);

        return redirect()->route('admin.learning.index', ['kind' => $data['kind']])->with('status', 'Video added.');
    }

    public function edit(LearningVideo $learning): View
    {
        return $this->form($learning);
    }

    public function update(LearningVideoRequest $request, LearningVideo $learning): RedirectResponse
    {
        $data = $request->safe()->except('thumbnail');
        $data['thumbnail_path'] = Media::replace($request->file('thumbnail'), $learning->thumbnail_path, 'video-thumbnails');
        $learning->update($data);

        return redirect()->route('admin.learning.index', ['kind' => $learning->kind])->with('status', 'Video saved.');
    }

    public function destroy(LearningVideo $learning): RedirectResponse
    {
        $learning->delete();
        Media::delete($learning->thumbnail_path);

        return back()->with('status', 'Video deleted.');
    }

    private function form(LearningVideo $video): View
    {
        return view('admin.learning.form', [
            'video' => $video,
            'courses' => Category::ofType('course')->orderBy('sort_order')->get(['id', 'name']),
        ]);
    }
}
