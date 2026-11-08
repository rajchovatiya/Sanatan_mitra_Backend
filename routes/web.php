<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DarshanController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FestivalController;
use App\Http\Controllers\Admin\GitaController;
use App\Http\Controllers\Admin\GuideController;
use App\Http\Controllers\Admin\LearningVideoController;
use App\Http\Controllers\Admin\LibraryItemController;
use App\Http\Controllers\Admin\MantraController;
use App\Http\Controllers\Admin\MuhuratDateController;
use App\Http\Controllers\Admin\PosterController;
use App\Http\Controllers\Admin\RashifalController;
use App\Http\Controllers\Admin\StudioMediaController;
use App\Http\Controllers\Admin\SuvicharController;
use App\Http\Controllers\Admin\TempleController;
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
        Route::resource('festivals', FestivalController::class)->except('show');
        Route::resource('rashifal', RashifalController::class)->except('show')->parameters(['rashifal' => 'rashifal']);
        Route::resource('temples', TempleController::class)->except('show');
        Route::resource('darshans', DarshanController::class)->except('show');
        Route::resource('library', LibraryItemController::class)->except('show');
        Route::resource('suvichars', SuvicharController::class)->except('show');
        Route::get('gita', [GitaController::class, 'chapters'])->name('gita.chapters');
        Route::get('gita/chapters/{chapter}/edit', [GitaController::class, 'editChapter'])->name('gita.chapters.edit');
        Route::put('gita/chapters/{chapter}', [GitaController::class, 'updateChapter'])->name('gita.chapters.update');
        Route::resource('gita-verses', GitaController::class)->except('show');
        Route::resource('muhurat', MuhuratDateController::class)->except('show');
        Route::resource('guides', GuideController::class)->except('show');
        Route::resource('studio', StudioMediaController::class)->except('show');
        Route::resource('learning', LearningVideoController::class)->except('show');
    });
});
