<?php

namespace App\Services\Push;

use Illuminate\Support\Facades\Log;

/** Development: writes the message to the log instead of sending it. */
class LogPushSender implements PushSender
{
    public function sendToTopic(string $topic, array $message): void
    {
        Log::info("Push to {$topic}: {$message['title']}", $message);
    }
}
