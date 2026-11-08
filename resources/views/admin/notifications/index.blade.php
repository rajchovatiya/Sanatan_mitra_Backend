@extends('admin.layout')
@section('title', 'Notifications')
@section('content')
    <div class="head">
        <h1>Notifications</h1>
        <a class="btn" href="{{ route('admin.notifications.create') }}">+ Send notification</a>
    </div>
    <p><small>Sent to every user who has notifications on, in their language. Sending runs in the background (the queue worker must be running).</small></p>
    <table>
        <thead><tr><th>Title</th><th>Message</th><th>Link</th><th>Status</th><th>By</th><th></th></tr></thead>
        <tbody>
        @forelse ($notifications as $n)
            <tr>
                <td>{{ $n->title['en'] ?? '' }}</td>
                <td>{{ \Illuminate\Support\Str::limit($n->body['en'] ?? '', 80) }}</td>
                <td><small>{{ $n->link }}</small></td>
                <td>{!! $n->sent_at ? '<span class="on">Sent '.e($n->sent_at->format('d M, H:i')).'</span>' : '<span class="off">Sending…</span>' !!}</td>
                <td>{{ $n->admin?->name }}</td>
                <td class="actions">
                    <form method="post" action="{{ route('admin.notifications.destroy', $n) }}" onsubmit="return confirm('Remove from the app list?')">
                        @csrf @method('DELETE')<button class="link danger">Remove</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6">Nothing sent yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $notifications->links('admin.pagination') }}
@endsection
