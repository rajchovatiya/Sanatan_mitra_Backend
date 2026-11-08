@extends('admin.layout')
@section('title', 'Daily Darshan')
@section('content')
    <div class="head">
        <h1>Daily Darshan</h1>
        <a class="btn" href="{{ route('admin.darshans.create') }}">+ Add darshan</a>
    </div>
    <p><small>The app shows the latest darshan dated today or earlier.</small></p>
    <table>
        <thead><tr><th></th><th>Date</th><th>Title</th><th>Temple</th><th>Video</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($darshans as $d)
            <tr>
                <td><img class="thumb" src="{{ \App\Support\Media::url($d->image_path) }}" alt=""></td>
                <td>{{ $d->date->format('d M Y') }}</td>
                <td>{{ $d->title['en'] ?? '' }}</td>
                <td>{{ $d->temple->name['en'] ?? '' }}</td>
                <td>{{ $d->video_url ? 'Yes' : '' }}</td>
                <td>{!! $d->is_active ? '<span class="on">On</span>' : '<span class="off">Off</span>' !!}</td>
                <td class="actions">
                    <a href="{{ route('admin.darshans.edit', $d) }}">Edit</a>
                    <form method="post" action="{{ route('admin.darshans.destroy', $d) }}" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')<button class="link danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7">No darshan yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $darshans->links('admin.pagination') }}
@endsection
