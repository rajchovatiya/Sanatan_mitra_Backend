<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SuvicharRequest;
use App\Models\Suvichar;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SuvicharController extends Controller
{
    public function index(): View
    {
        $suvichars = Suvichar::orderByDesc('date')->latest('id')->paginate(30);

        return view('admin.suvichars.index', compact('suvichars'));
    }

    public function create(): View
    {
        return view('admin.suvichars.form', ['suvichar' => new Suvichar]);
    }

    public function store(SuvicharRequest $request): RedirectResponse
    {
        Suvichar::create($request->validated());

        return redirect()->route('admin.suvichars.index')->with('status', 'Suvichar added.');
    }

    public function edit(Suvichar $suvichar): View
    {
        return view('admin.suvichars.form', compact('suvichar'));
    }

    public function update(SuvicharRequest $request, Suvichar $suvichar): RedirectResponse
    {
        $suvichar->update($request->validated());

        return redirect()->route('admin.suvichars.index')->with('status', 'Suvichar saved.');
    }

    public function destroy(Suvichar $suvichar): RedirectResponse
    {
        $suvichar->delete();

        return back()->with('status', 'Suvichar deleted.');
    }
}
