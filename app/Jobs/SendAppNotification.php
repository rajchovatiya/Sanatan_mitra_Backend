<?php

namespace App\Jobs;

use App\Models\AppNotification;
use App\Services\Push\PushSender;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/** Sends one notification to every language topic, in the background. */
class SendAppNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public AppNotification $notification) {}

    public function handle(PushSender $push): void
    {
        foreach (config('sanatan.languages') as $lang) {
            $push->sendToTopic("all_{$lang}", $this->notification->message($lang));
        }

        $this->notification->forceFill(['sent_at' => now()])->save();
    }
}
