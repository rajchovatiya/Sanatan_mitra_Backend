<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MantraController;
use App\Http\Controllers\Admin\PosterController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

/* Admin panel: HTML pages, session login (admins table), never used by the app. */
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AuthController::class, 'create'])->name('login');
        Route::post('login', [AuthController::class, 'store'])->middleware('throttle:admin-login')->name('login.store');
    });

    Route::middleware(['auth:admin', 'can:manage-content'])->group(function () {
        Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('posters', PosterController::class)->except('show');
        Route::resource('mantras', MantraController::class)->except('show');
    });
});
