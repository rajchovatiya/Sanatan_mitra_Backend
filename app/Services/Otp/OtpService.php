<?php

namespace App\Services\Otp;

use Illuminate\Support\Facades\Cache;

/**
 * Creates and checks login codes. Only a keyed hash of the code is stored, and a
 * code is thrown away after a successful check or too many wrong attempts.
 */
class OtpService
{
    public function __construct(private OtpSender $sender) {}

    /** Sends a new code (replacing any earlier one) and returns it. */
    public function send(string $mobile): string
    {
        $length = config('sanatan.otp.length');
        $code = (string) random_int(10 ** ($length - 1), 10 ** $length - 1);
        $expiresAt = now()->addSeconds(config('sanatan.otp.ttl'));

        Cache::put($this->key($mobile), [
            'hash' => $this->hash($mobile, $code),
            'attempts' => 0,
            'expires_at' => $expiresAt->getTimestamp(),
        ], $expiresAt);

        $this->sender->send($mobile, $code);

        return $code;
    }

    public function verify(string $mobile, string $code): bool
    {
        $key = $this->key($mobile);
        $entry = Cache::get($key);

        if (! $entry) {
            return false;
        }

        if (hash_equals($entry['hash'], $this->hash($mobile, $code))) {
            Cache::forget($key);

            return true;
        }

        $entry['attempts']++;
        if ($entry['attempts'] >= config('sanatan.otp.max_attempts')) {
            Cache::forget($key);
        } else {
            Cache::put($key, $entry, now()->setTimestamp($entry['expires_at']));
        }

        return false;
    }

    private function key(string $mobile): string
    {
        return 'otp:'.$mobile;
    }

    private function hash(string $mobile, string $code): string
    {
        return hash_hmac('sha256', $mobile.'|'.$code, config('app.key'));
    }
}
