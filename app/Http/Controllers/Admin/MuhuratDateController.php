<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MuhuratDateRequest;
use App\Models\Category;
use App\Models\MuhuratDate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MuhuratDateController extends Controller
{
    public function index(Request $request): View
    {
        $q = $request->validate(['category' => ['nullable', 'integer'], 'year' => ['nullable', 'integer', 'between:2000,2100']]);
        $year = $q['year'] ?? today()->year;
        $category = $q['category'] ?? null;

        $dates = MuhuratDate::with('category:id,name')
            ->when($category, fn ($w) => $w->where('category_id', $category))
            ->whereBetween('date', ["{$year}-01-01", "{$year}-12-31 23:59:59"])
            ->orderBy('date')
            ->paginate(60)->withQueryString();

        return view('admin.muhurat.index', [
            'dates' => $dates, 'year' => $year, 'category' => $category, 'categories' => $this->types(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new MuhuratDate(['date' => today()->toDateString()]));
    }

    public function store(MuhuratDateRequest $request): RedirectResponse
    {
        $d = MuhuratDate::create($request->validated());

        return redirect()->route('admin.muhurat.index', ['category' => $d->category_id, 'year' => $d->date->year])
            ->with('status', 'Muhurat date added.');
    }

    public function edit(MuhuratDate $muhurat): View
    {
        return $this->form($muhurat);
    }

    public function update(MuhuratDateRequest $request, MuhuratDate $muhurat): RedirectResponse
    {
        $muhurat->update($request->validated());

        return redirect()->route('admin.muhurat.index', ['category' => $muhurat->category_id, 'year' => $muhurat->date->year])
            ->with('status', 'Muhurat date saved.');
    }

    public function destroy(MuhuratDate $muhurat): RedirectResponse
    {
        $muhurat->delete();

        return back()->with('status', 'Muhurat date deleted.');
    }

    private function form(MuhuratDate $date): View
    {
        return view('admin.muhurat.form', ['muhurat' => $date, 'categories' => $this->types()]);
    }

    private function types()
    {
        return Category::ofType('muhurat')->orderBy('sort_order')->get(['id', 'name']);
    }
}
