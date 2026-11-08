<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->validate(['type' => ['nullable', Rule::in(array_keys(Category::TYPES))]])['type'] ?? null;

        $categories = Category::query()
            ->when($type, fn ($q) => $q->ofType($type))
            ->withCount(['posters', 'mantras'])
            ->orderBy('type')->orderBy('sort_order')->orderBy('id')
            ->paginate(30)->withQueryString();

        return view('admin.categories.index', compact('categories', 'type'));
    }

    public function create(Request $request): View
    {
        return view('admin.categories.form', ['category' => new Category([
            'type' => $request->query('type', 'poster'), 'is_active' => true,
        ])]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('icon');
        $data['icon_path'] = $request->hasFile('icon') ? Media::store($request->file('icon'), 'category-icons') : null;
        Category::create($data);

        return redirect()->route('admin.categories.index', ['type' => $data['type']])->with('status', 'Category added.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        // The module is fixed after creation, so its items never end up under the wrong type.
        $data = $request->safe()->except(['icon', 'type']);
        $data['icon_path'] = Media::replace($request->file('icon'), $category->icon_path, 'category-icons');
        $category->update($data);

        return redirect()->route('admin.categories.index', ['type' => $category->type])->with('status', 'Category saved.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->posters()->exists() || $category->mantras()->exists()) {
            return back()->withErrors(['category' => 'Move or delete the items in this category first, or just turn it off.']);
        }

        $category->delete();
        Media::delete($category->icon_path);

        return back()->with('status', 'Category deleted.');
    }
}
