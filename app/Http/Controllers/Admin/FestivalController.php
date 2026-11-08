<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FestivalRequest;
use App\Models\Festival;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class FestivalController extends Controller
{
    public function index(Request $request): View
    {
        $month = $request->validate(['month' => ['nullable', 'date_format:Y-m']])['month'] ?? today()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m-d', "{$month}-01")->startOfDay();

        $festivals = Festival::query()
            ->whereBetween('date', [$start->toDateString(), $start->copy()->endOfMonth()->toDateTimeString()])
            ->orderBy('date')->orderBy('sort_order')
            ->get(['id', 'is_vrat', 'name', 'tithi', 'date', 'image_path', 'is_active']);

        return view('admin.festivals.index', [
            'festivals' => $festivals,
            'month' => $start,
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.festivals.form', ['festival' => new Festival(['date' => $request->query('date', today()->toDateString())])]);
    }

    public function store(FestivalRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $data['image_path'] = $request->hasFile('image') ? Media::store($request->file('image'), 'festivals') : null;
        $festival = Festival::create($data);

        return $this->backToMonth($festival, 'Festival added.');
    }

    public function edit(Festival $festival): View
    {
        return view('admin.festivals.form', compact('festival'));
    }

    public function update(FestivalRequest $request, Festival $festival): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $data['image_path'] = Media::replace($request->file('image'), $festival->image_path, 'festivals');
        $festival->update($data);

        return $this->backToMonth($festival, 'Festival saved.');
    }

    public function destroy(Festival $festival): RedirectResponse
    {
        $festival->delete();
        Media::delete($festival->image_path);

        return back()->with('status', 'Festival deleted.');
    }

    private function backToMonth(Festival $festival, string $message): RedirectResponse
    {
        return redirect()->route('admin.festivals.index', ['month' => $festival->date->format('Y-m')])->with('status', $message);
    }
}
