<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Festival;
use App\Models\Mantra;
use App\Models\Poster;
use App\Models\Rashifal;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', ['counts' => [
            'App users' => User::count(),
            'Categories' => Category::count(),
            'Posters' => Poster::count(),
            'Mantras' => Mantra::count(),
            'Festivals' => Festival::count(),
            "Today's rashifal" => Rashifal::whereDate('date', today())->count().' / 12',
        ]]);
    }
}
