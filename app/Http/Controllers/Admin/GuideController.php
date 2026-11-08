<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GuideRequest;
use App\Models\Guide;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GuideController extends Controller
{
    public function index(Request $request): View
    {
        $module = $request->validate(['module' => ['nullable', Rule::in(array_keys(Guide::MODULES))]])['module'] ?? 'gems';

        $guides = Guide::where('module', $module)
            ->select(['id', 'module', 'title', 'subtitle', 'image_path', 'sort_order', 'is_active'])
            ->orderBy('sort_order')->orderBy('id')
            ->paginate(50)->withQueryString();

        return view('admin.guides.index', compact('guides', 'module'));
    }

    public function create(Request $request): View
    {
        return view('admin.guides.form', ['guide' => new Guide(['module' => $request->query('module', 'gems')])]);
    }

    public function store(GuideRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $data['image_path'] = $request->hasFile('image') ? Media::store($request->file('image'), 'guides') : null;
        $guide = Guide::create($data);

        return redirect()->route('admin.guides.index', ['module' => $guide->module])->with('status', 'Added.');
    }

    public function edit(Guide $guide): View
    {
        return view('admin.guides.form', compact('guide'));
    }

    public function update(GuideRequest $request, Guide $guide): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $data['image_path'] = Media::replace($request->file('image'), $guide->image_path, 'guides');
        $guide->update($data);

        return redirect()->route('admin.guides.index', ['module' => $guide->module])->with('status', 'Saved.');
    }

    public function destroy(Guide $guide): RedirectResponse
    {
        $guide->delete();
        Media::delete($guide->image_path);

        return back()->with('status', 'Deleted.');
    }
}
