<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Support\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class NotificationController extends Controller
{
    /** Sent notifications, newest first, each with "unread"; also the total unread count. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $readAt = $request->user()->notifications_read_at;
        $sent = AppNotification::whereNotNull('sent_at');

        $page = (clone $sent)->latest('sent_at')->paginate(30, ['id', 'title', 'body', 'image_path', 'link', 'sent_at']);

        return JsonResource::collection($page->through(fn (AppNotification $n) => [
            'id' => $n->id,
            'title' => $n->tr('title'),
            'body' => $n->tr('body'),
            'image_url' => Media::url($n->image_path),
            'link' => $n->link,
            'sent_at' => $n->sent_at->toIso8601String(),
            'unread' => ! $readAt || $n->sent_at->gt($readAt),
        ]))->additional(['unread_count' => $readAt ? (clone $sent)->where('sent_at', '>', $readAt)->count() : (clone $sent)->count()]);
    }

    public function markRead(Request $request): Response
    {
        $request->user()->forceFill(['notifications_read_at' => now()])->save();

        return response()->noContent();
    }
}
