<?php

namespace App\Providers;

use App\Models\Admin;
use App\Services\Astrology\AstrologyProvider;
use App\Services\Astrology\NullAstrologyProvider;
use App\Services\Otp\LogOtpSender;
use App\Services\Otp\OtpSender;
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

        $this->app->bind(AstrologyProvider::class, fn () => match (config('sanatan.astrology.driver')) {
            'none' => new NullAstrologyProvider,
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

        RateLimiter::for('admin-login', fn (Request $r) => Limit::perMinute(5)->by(
            strtolower((string) $r->input('email')).'|'.$r->ip()
        ));

        // Provider calls may cost money; cached answers are cheap but still limited.
        RateLimiter::for('astro', fn (Request $r) => Limit::perMinute(30)->by($r->user()?->id ?: $r->ip()));
    }
}
