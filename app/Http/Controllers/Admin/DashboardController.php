<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Mantra;
use App\Models\Poster;
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
        ]]);
    }
}
