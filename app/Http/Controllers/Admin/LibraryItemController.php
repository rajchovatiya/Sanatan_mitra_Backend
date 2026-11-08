<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LibraryItemRequest;
use App\Models\LibraryItem;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LibraryItemController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->validate(['type' => ['nullable', Rule::in(array_keys(LibraryItem::TYPES))]])['type'] ?? null;

        $items = LibraryItem::query()
            ->when($type, fn ($q) => $q->where('type', $type))
            ->select(['id', 'type', 'title', 'audio_path', 'sort_order', 'is_active'])
            ->orderBy('type')->orderBy('sort_order')->orderBy('id')
            ->paginate(30)->withQueryString();

        return view('admin.library.index', compact('items', 'type'));
    }

    public function create(Request $request): View
    {
        return view('admin.library.form', ['item' => new LibraryItem(['type' => $request->query('type', 'katha')])]);
    }

    public function store(LibraryItemRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'audio']);
        $data['image_path'] = $request->hasFile('image') ? Media::store($request->file('image'), 'library-images') : null;
        $data['audio_path'] = $request->hasFile('audio') ? Media::store($request->file('audio'), 'library-audio') : null;
        LibraryItem::create($data);

        return redirect()->route('admin.library.index', ['type' => $data['type']])->with('status', 'Added.');
    }

    public function edit(LibraryItem $library): View
    {
        return view('admin.library.form', ['item' => $library]);
    }

    public function update(LibraryItemRequest $request, LibraryItem $library): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'audio']);
        $data['image_path'] = Media::replace($request->file('image'), $library->image_path, 'library-images');
        $data['audio_path'] = Media::replace($request->file('audio'), $library->audio_path, 'library-audio');
        $library->update($data);

        return redirect()->route('admin.library.index', ['type' => $library->type])->with('status', 'Saved.');
    }

    public function destroy(LibraryItem $library): RedirectResponse
    {
        $library->delete();
        Media::delete($library->image_path, $library->audio_path);

        return back()->with('status', 'Deleted.');
    }
}
