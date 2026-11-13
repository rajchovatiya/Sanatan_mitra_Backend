<?php

namespace App\Providers;

use App\Models\Admin;
use App\Services\Astrology\AstrologyProvider;
use App\Services\Astrology\NullAstrologyProvider;
use App\Services\Astrology\ProkeralaProvider;
use App\Services\Otp\LogOtpSender;
use App\Services\Otp\OtpSender;
use App\Services\Push\FcmPushSender;
use App\Services\Push\LogPushSender;
use App\Services\Push\PushSender;
use App\Services\Translation\GoogleTranslator;
use App\Services\Translation\NullTranslator;
use App\Services\Translation\Translator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OtpSender::class, fn () => match (config('sanatan.otp.driver')) {
            'log' => new LogOtpSender,
            default => throw new InvalidArgumentException('Unknown OTP_DRIVER.'),
        });

        $this->app->bind(PushSender::class, fn () => match (config('sanatan.push.driver')) {
            'log' => new LogPushSender,
            'fcm' => new FcmPushSender(config('sanatan.push.credentials')),
            default => throw new InvalidArgumentException('Unknown PUSH_DRIVER.'),
        });

        $this->app->bind(Translator::class, fn () => match (config('sanatan.translate.driver')) {
            'none' => new NullTranslator,
            'google' => new GoogleTranslator((string) config('sanatan.translate.google_key')),
            default => throw new InvalidArgumentException('Unknown TRANSLATE_DRIVER.'),
        });

        $this->app->bind(AstrologyProvider::class, fn () => match (config('sanatan.astrology.driver')) {
            'none' => new NullAstrologyProvider,
            'prokerala' => new ProkeralaProvider(
                (string) config('sanatan.astrology.prokerala.client_id'),
                (string) config('sanatan.astrology.prokerala.client_secret'),
            ),
            default => throw new InvalidArgumentException('Unknown ASTROLOGY_DRIVER.'),
        });
    }

    public function boot(): void
    {
        $this->configureRateLimits();

        // Every active admin (super_admin or editor) may manage app content.
        Gate::define('manage-content', fn ($user) => $user instanceof Admin && $user->is_active);
        // Only super admins manage settings and other admins.
        Gate::define('manage-admins', fn ($user) => $user instanceof Admin && $user->is_active && $user->isSuper());
    }

    private function configureRateLimits(): void
    {
        $mobile = fn (Request $r): string => is_string($m = $r->input('mobile')) ? $m : '';

        // Every API call: generous for a real user, stops scripts.
        RateLimiter::for('api', fn (Request $r) => Limit::perMinute(120)->by($r->user()?->id ?: $r->ip()));

        // OTP SMS cost money and can be abused to spam a number.
        RateLimiter::for('otp-send', fn (Request $r) => [
            Limit::perMinute(1)->by('otp-send-1m:'.$mobile($r)),
            Limit::perHour(5)->by('otp-send-1h:'.$mobile($r)),
            Limit::perHour(30)->by('otp-send-ip:'.$r->ip()),
        ]);

        RateLimiter::for('otp-verify', fn (Request $r) => [
            Limit::perMinute(10)->by('otp-verify-ip:'.$r->ip()),
            Limit::perHour(30)->by('otp-verify:'.$mobile($r)),
        ]);

        // Translation is paid per character.
        RateLimiter::for('admin-translate', fn (Request $r) => Limit::perMinute(30)->by('translate:'.$r->user('admin')?->id));

        RateLimiter::for('admin-login', fn (Request $r) => Limit::perMinute(5)->by(
            strtolower((string) $r->input('email')).'|'.$r->ip()
        ));

        // Search scans text, feedback can be spammed.
        RateLimiter::for('search', fn (Request $r) => Limit::perMinute(30)->by($r->user()?->id ?: $r->ip()));
        // Place search runs as the user types (the app waits for a pause first).
        RateLimiter::for('places', fn (Request $r) => Limit::perMinute(60)->by($r->user()?->id ?: $r->ip()));
        RateLimiter::for('feedback', fn (Request $r) => Limit::perHour(5)->by($r->user()?->id ?: $r->ip()));

        // Provider calls may cost money; cached answers are cheap but still limited.
        RateLimiter::for('astro', fn (Request $r) => Limit::perMinute(30)->by($r->user()?->id ?: $r->ip()));
    }
}
