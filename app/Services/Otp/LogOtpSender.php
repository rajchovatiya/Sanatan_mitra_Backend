<?php

namespace App\Services\Otp;

use Illuminate\Support\Facades\Log;

/** Development only: writes the code to storage/logs instead of sending an SMS. */
class LogOtpSender implements OtpSender
{
    public function send(string $mobile, string $code): void
    {
        Log::info("Login OTP for {$mobile}: {$code}");
    }
}
