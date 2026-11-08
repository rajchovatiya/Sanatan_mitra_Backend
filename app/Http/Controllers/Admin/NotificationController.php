<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TranslatedRules;
use App\Jobs\SendAppNotification;
use App\Models\AppNotification;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Notifications are sent once and kept as history; they are not edited afterwards. */
class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = AppNotification::with('admin:id,name')->latest('id')->paginate(30);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function create(): View
    {
        return view('admin.notifications.form');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            ...TranslatedRules::for('title', max: 80),
            ...TranslatedRules::for('body', max: 300),
            'link' => ['nullable', 'string', 'max:255', 'regex:#^(/[A-Za-z0-9/_\-?=&]*|https://\S+)$#'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:1024'],
        ], ['link.regex' => 'Use an app path like /festivals/5 or an https:// link.']);

        $notification = AppNotification::create([
            'title' => $data['title'],
            'body' => $data['body'],
            'link' => $data['link'] ?? null,
            'image_path' => $request->hasFile('image') ? Media::store($request->file('image'), 'notifications') : null,
            'admin_id' => $request->user('admin')->id,
        ]);

        SendAppNotification::dispatch($notification);

        return redirect()->route('admin.notifications.index')->with('status', 'Notification is being sent.');
    }

    public function destroy(AppNotification $notification): RedirectResponse
    {
        $notification->delete();
        Media::delete($notification->image_path);

        return back()->with('status', 'Removed from the app list (already-delivered pushes stay on phones).');
    }
}
