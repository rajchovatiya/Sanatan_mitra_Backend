@extends('admin.layout')
@section('title', 'Feedback')
@section('content')
    <h1>Feedback from app users</h1>
    <table>
        <thead><tr><th>Date</th><th>From</th><th>Message</th><th>App</th><th></th></tr></thead>
        <tbody>
        @forelse ($feedback as $f)
            <tr>
                <td>{{ $f->created_at->format('d M Y, H:i') }}@unless ($f->is_seen)<br><span class="on">New</span>@endunless</td>
                <td>{{ $f->user?->name ?? 'Guest' }}<br><small>{{ $f->user?->mobile }} {{ $f->contact }}</small></td>
                <td style="white-space: pre-line">{{ $f->message }}</td>
                <td><small>{{ $f->platform }} {{ $f->app_version }}</small></td>
                <td class="actions">
                    @unless ($f->is_seen)
                        <form method="post" action="{{ route('admin.feedback.seen', $f) }}">@csrf @method('PATCH')<button class="link">Mark seen</button></form>
                    @endunless
                    <form method="post" action="{{ route('admin.feedback.destroy', $f) }}" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')<button class="link danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5">No feedback yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $feedback->links('admin.pagination') }}
@endsection
