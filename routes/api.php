<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AppConfigController;
use App\Http\Controllers\Api\AstrologyController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\KundliController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

/*
| Mobile app API. JSON only. Versioned so older app builds keep working when v2 arrives.
| Full list with request and response examples: docs/API.md
*/
Route::prefix('v1')->name('api.v1.')->group(function () {

    Route::get('app/config', AppConfigController::class)->name('app.config');

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

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        Route::get('me', [ProfileController::class, 'show'])->name('me.show');
        Route::patch('me', [ProfileController::class, 'update'])->name('me.update');
        Route::post('me/photo', [ProfileController::class, 'updatePhoto'])->name('me.photo');
        Route::post('me/business-logo', [ProfileController::class, 'updateBusinessLogo'])->name('me.business-logo');
        Route::post('me/devices', [DeviceController::class, 'store'])->name('me.devices.store');
        Route::apiResource('me/kundlis', KundliController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['kundlis' => 'kundli'])
            ->names('me.kundlis');

        Route::delete('account', [AccountController::class, 'destroy'])->name('account.destroy');

        Route::middleware('throttle:astro')->group(function () {
            Route::post('astro/kundli', [AstrologyController::class, 'kundli'])->name('astro.kundli');
            Route::post('astro/matching', [AstrologyController::class, 'matching'])->name('astro.matching');
        });
    });
});
