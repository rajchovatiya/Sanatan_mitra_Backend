<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DarshanRequest;
use App\Models\Darshan;
use App\Models\Temple;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DarshanController extends Controller
{
    public function index(): View
    {
        $darshans = Darshan::with('temple:id,name')->latest('date')->latest('id')->paginate(30);

        return view('admin.darshans.index', compact('darshans'));
    }

    public function create(): View
    {
        return $this->form(new Darshan(['date' => today()->toDateString()]));
    }

    public function store(DarshanRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $data['image_path'] = Media::store($request->file('image'), 'darshans');
        Darshan::create($data);

        return redirect()->route('admin.darshans.index')->with('status', 'Darshan added.');
    }

    public function edit(Darshan $darshan): View
    {
        return $this->form($darshan);
    }

    public function update(DarshanRequest $request, Darshan $darshan): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $data['image_path'] = Media::replace($request->file('image'), $darshan->image_path, 'darshans');
        $darshan->update($data);

        return redirect()->route('admin.darshans.index')->with('status', 'Darshan saved.');
    }

    public function destroy(Darshan $darshan): RedirectResponse
    {
        $darshan->delete();
        Media::delete($darshan->image_path);

        return back()->with('status', 'Darshan deleted.');
    }

    private function form(Darshan $darshan): View
    {
        return view('admin.darshans.form', [
            'darshan' => $darshan,
            'temples' => Temple::orderBy('sort_order')->get(['id', 'name']),
        ]);
    }
}
