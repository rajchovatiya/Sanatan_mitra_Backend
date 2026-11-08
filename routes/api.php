<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AppConfigController;
use App\Http\Controllers\Api\AstrologyController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\FestivalController;
use App\Http\Controllers\Api\GuideController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\KundliController;
use App\Http\Controllers\Api\LibraryController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RashifalController;
use App\Http\Controllers\Api\SupportController;
use App\Http\Controllers\Api\TempleController;
use Illuminate\Support\Facades\Route;

/*
| Mobile app API. JSON only. Versioned so older app builds keep working when v2 arrives.
| Full list with request and response examples: docs/API.md
*/
Route::prefix('v1')->name('api.v1.')->group(function () {

    Route::get('app/config', AppConfigController::class)->name('app.config');
    Route::get('home', HomeController::class)->name('home');
    Route::get('search', [SupportController::class, 'search'])->middleware('throttle:search')->name('search');
    Route::post('feedback', [SupportController::class, 'feedback'])->middleware('throttle:feedback')->name('feedback');
    Route::get('pages/{slug}', [SupportController::class, 'page'])->whereAlpha('slug')->name('pages.show');

    Route::post('auth/otp/send', [AuthController::class, 'sendOtp'])
        ->middleware('throttle:otp-send')->name('auth.otp.send');
    Route::post('auth/otp/verify', [AuthController::class, 'verifyOtp'])
        ->middleware('throttle:otp-verify')->name('auth.otp.verify');

    Route::get('astro/panchang', [AstrologyController::class, 'panchang'])
        ->middleware('throttle:astro')->name('astro.panchang');

    Route::get('posters/categories', [ContentController::class, 'categories'])
        ->defaults('type', 'poster')->name('posters.categories');
    Route::get('posters', [ContentController::class, 'posters'])->name('posters.index');
    Route::get('mantras/categories', [ContentController::class, 'categories'])
        ->defaults('type', 'mantra')->name('mantras.categories');
    Route::get('mantras', [ContentController::class, 'mantras'])->name('mantras.index');
    Route::get('mantras/{mantra}', [ContentController::class, 'mantra'])->whereNumber('mantra')->name('mantras.show');

    Route::get('festivals', [FestivalController::class, 'index'])->name('festivals.index');
    Route::get('festivals/upcoming', [FestivalController::class, 'upcoming'])->name('festivals.upcoming');
    Route::get('festivals/{festival}', [FestivalController::class, 'show'])->whereNumber('festival')->name('festivals.show');

    Route::get('rashifal', [RashifalController::class, 'index'])->name('rashifal.index');

    Route::get('temples', [TempleController::class, 'index'])->name('temples.index');
    Route::get('temples/{temple}', [TempleController::class, 'show'])->whereNumber('temple')->name('temples.show');
    Route::get('darshan/today', [TempleController::class, 'darshanToday'])->name('darshan.today');

    Route::get('library', [LibraryController::class, 'index'])->name('library.index');
    Route::get('library/{item}', [LibraryController::class, 'show'])->whereNumber('item')->name('library.show');
    Route::get('gita/chapters', [LibraryController::class, 'chapters'])->name('gita.chapters');
    Route::get('gita/chapters/{chapter}/verses', [LibraryController::class, 'verses'])->whereNumber('chapter')->name('gita.verses');
    Route::get('gita/verse-of-the-day', [LibraryController::class, 'verseOfTheDay'])->name('gita.daily');
    Route::get('suvichar/today', [LibraryController::class, 'suvichar'])->name('suvichar.today');

    Route::get('muhurat/categories', [ContentController::class, 'categories'])
        ->defaults('type', 'muhurat')->name('muhurat.categories');
    Route::get('muhurat', [GuideController::class, 'muhuratDates'])->name('muhurat.dates');
    Route::get('wallpapers/categories', [ContentController::class, 'categories'])->defaults('type', 'wallpaper')->name('wallpapers.categories');
    Route::get('wallpapers', [MediaController::class, 'studio'])->defaults('kind', 'wallpaper')->name('wallpapers.index');
    Route::get('videos/categories', [ContentController::class, 'categories'])->defaults('type', 'video')->name('videos.categories');
    Route::get('videos', [MediaController::class, 'studio'])->defaults('kind', 'video')->name('videos.index');
    Route::get('live', [MediaController::class, 'live'])->name('live.index');
    Route::get('courses', [ContentController::class, 'categories'])->defaults('type', 'course')->name('courses.index');
    Route::get('courses/{course}', [MediaController::class, 'course'])->whereNumber('course')->name('courses.show');

    Route::get('guides/{module}', [GuideController::class, 'index'])->whereAlpha('module')->name('guides.index');
    Route::get('guides/{module}/{guide}', [GuideController::class, 'show'])
        ->whereAlpha('module')->whereNumber('guide')->name('guides.show');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        Route::get('me', [ProfileController::class, 'show'])->name('me.show');
        Route::patch('me', [ProfileController::class, 'update'])->name('me.update');
        Route::post('me/photo', [ProfileController::class, 'updatePhoto'])->name('me.photo');
        Route::post('me/business-logo', [ProfileController::class, 'updateBusinessLogo'])->name('me.business-logo');
        Route::post('me/devices', [DeviceController::class, 'store'])->name('me.devices.store');
        Route::get('me/favorites', [FavoriteController::class, 'index'])->name('me.favorites.index');
        Route::post('me/favorites', [FavoriteController::class, 'store'])->name('me.favorites.store');
        Route::delete('me/favorites/{type}/{itemId}', [FavoriteController::class, 'destroy'])
            ->whereAlpha('type')->whereNumber('itemId')->name('me.favorites.destroy');
        Route::apiResource('me/kundlis', KundliController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['kundlis' => 'kundli'])
            ->names('me.kundlis');

        Route::delete('account', [AccountController::class, 'destroy'])->name('account.destroy');

        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/read', [NotificationController::class, 'markRead'])->name('notifications.read');

        Route::middleware('throttle:astro')->group(function () {
            Route::post('astro/kundli', [AstrologyController::class, 'kundli'])->name('astro.kundli');
            Route::post('astro/matching', [AstrologyController::class, 'matching'])->name('astro.matching');
        });
    });
});
