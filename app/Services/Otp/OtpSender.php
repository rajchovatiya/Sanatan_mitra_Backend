<?php

namespace App\Services\Otp;

/** Delivers a login code to a mobile number (log for development, SMS provider later). */
interface OtpSender
{
    public function send(string $mobile, string $code): void;
}
