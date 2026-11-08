<?php

namespace Tests\Fakes;

use App\Services\Otp\OtpSender;

class FakeOtpSender implements OtpSender
{
    /** @var array<string, string> last code per mobile */
    public array $sent = [];

    public function send(string $mobile, string $code): void
    {
        $this->sent[$mobile] = $code;
    }
}
