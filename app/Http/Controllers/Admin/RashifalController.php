<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RashifalRequest;
use App\Models\Rashifal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** The admin works one day at a time: the day page shows all 12 rashis and which are still missing. */
class RashifalController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->validate(['date' => ['nullable', 'date_format:Y-m-d']])['date'] ?? today()->toDateString();

        $written = Rashifal::whereDate('date', $date)->get(['id', 'rashi', 'overall', 'updated_at'])->keyBy('rashi');

        return view('admin.rashifal.index', compact('date', 'written'));
    }

    public function create(Request $request): View
    {
        $q = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'rashi' => ['nullable', 'integer', 'between:0,11'],
        ]);

        return view('admin.rashifal.form', ['rashifal' => new Rashifal([
            'date' => $q['date'] ?? today()->toDateString(),
            'rashi' => $q['rashi'] ?? 0,
        ])]);
    }

    public function store(RashifalRequest $request): RedirectResponse
    {
        $rashifal = Rashifal::create($request->payload());

        return $this->backToDay($rashifal, 'Rashifal added.');
    }

    public function edit(Rashifal $rashifal): View
    {
        return view('admin.rashifal.form', compact('rashifal'));
    }

    public function update(RashifalRequest $request, Rashifal $rashifal): RedirectResponse
    {
        $rashifal->update($request->payload());

        return $this->backToDay($rashifal, 'Rashifal saved.');
    }

    public function destroy(Rashifal $rashifal): RedirectResponse
    {
        $rashifal->delete();

        return $this->backToDay($rashifal, 'Rashifal deleted.');
    }

    private function backToDay(Rashifal $rashifal, string $message): RedirectResponse
    {
        return redirect()->route('admin.rashifal.index', ['date' => $rashifal->date->toDateString()])->with('status', $message);
    }
}
