<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AppUserController;
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
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PosterController;
use App\Http\Controllers\Admin\RashifalController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StudioMediaController;
use App\Http\Controllers\Admin\SupportController;
use App\Http\Controllers\Admin\SuvicharController;
use App\Http\Controllers\Admin\TempleController;
use App\Http\Controllers\Admin\TranslateController;
use App\Http\Controllers\PublicPageController;
use App\Http\Middleware\AuditAdminChanges;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

/* Public pages for the app stores (privacy policy, terms, account deletion). */
Route::name('public.')->group(function () {
    Route::get('privacy', [PublicPageController::class, 'privacy'])->name('privacy');
    Route::get('terms', [PublicPageController::class, 'terms'])->name('terms');
    Route::get('delete-account', [PublicPageController::class, 'deleteAccount'])->name('delete-account');
    Route::post('delete-account', [PublicPageController::class, 'requestDeletion'])
        ->middleware('throttle:feedback')->name('delete-account.request');
});

/* Admin panel: HTML pages, session login (admins table), never used by the app. */
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AuthController::class, 'create'])->name('login');
        Route::post('login', [AuthController::class, 'store'])->middleware('throttle:admin-login')->name('login.store');
    });

    Route::middleware(['auth:admin', 'can:manage-content', AuditAdminChanges::class])->group(function () {
        Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::post('translate', TranslateController::class)->middleware('throttle:admin-translate')->name('translate');

        // Content (super admins and editors).
        Route::resource('categories', CategoryController::class)->except('show');
        Route::get('posters/bulk', [PosterController::class, 'bulkCreate'])->name('posters.bulk');
        Route::post('posters/bulk', [PosterController::class, 'bulkStore'])->name('posters.bulk.store');
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

        // Users and communication.
        Route::resource('notifications', NotificationController::class)->only(['index', 'create', 'store', 'destroy']);
        Route::get('feedback', [SupportController::class, 'feedback'])->name('feedback.index');
        Route::patch('feedback/{feedback}', [SupportController::class, 'markSeen'])->name('feedback.seen');
        Route::delete('feedback/{feedback}', [SupportController::class, 'destroyFeedback'])->name('feedback.destroy');
        Route::get('pages', [SupportController::class, 'pages'])->name('pages.index');
        Route::get('pages/{page}/edit', [SupportController::class, 'editPage'])->name('pages.edit');
        Route::put('pages/{page}', [SupportController::class, 'updatePage'])->name('pages.update');
        Route::get('users', [AppUserController::class, 'index'])->name('users.index');
        Route::get('users/{user}', [AppUserController::class, 'show'])->name('users.show');
        Route::patch('users/{user}/block', [AppUserController::class, 'toggleBlock'])->name('users.block');

        // Super admin only.
        Route::middleware('can:manage-admins')->group(function () {
            Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
            Route::resource('admins', AdminUserController::class)->except(['show', 'destroy']);
            Route::get('audit', [AdminUserController::class, 'audit'])->name('audit.index');
            Route::delete('users/{user}', [AppUserController::class, 'destroy'])->name('users.destroy');
        });
    });
});
