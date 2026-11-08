<?php

namespace App\Services\Push;

/**
 * Sends a push message to a topic. The app subscribes to "all_<language>" (all_en, all_hi, all_gu)
 * and unsubscribes when the user turns notifications off, so one call reaches every phone.
 *
 * $message: {title, body, image (url or null), data (string => string)}
 */
interface PushSender
{
    public function sendToTopic(string $topic, array $message): void;
}
