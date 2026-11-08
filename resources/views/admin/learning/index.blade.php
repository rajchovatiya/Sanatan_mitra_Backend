@extends('admin.layout')
@section('title', 'Live & Courses')
@section('content')
    <div class="head">
        <h1>{{ \App\Models\LearningVideo::KINDS[$kind] }}</h1>
        <a class="btn" href="{{ route('admin.learning.create', ['kind' => $kind]) }}">+ Add video</a>
    </div>
    <p class="tabs">
        @foreach (\App\Models\LearningVideo::KINDS as $key => $label)
            <a href="{{ route('admin.learning.index', ['kind' => $key]) }}" @class(['active' => $kind === $key])>{{ $label }}</a>
        @endforeach
        <a href="{{ route('admin.categories.index', ['type' => 'course']) }}">Manage courses</a>
    </p>
    <table>
        <thead><tr><th>Title</th>@if ($kind === 'lesson')<th>Course</th>@endif<th>Link</th><th>Order</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($videos as $v)
            <tr>
                <td>{{ $v->title['en'] ?? '' }}</td>
                @if ($kind === 'lesson')<td>{{ $v->category->name['en'] ?? '' }}</td>@endif
                <td><a href="{{ $v->video_url }}" target="_blank" rel="noopener">Open</a></td>
                <td>{{ $v->sort_order }}</td>
                <td>{!! $v->is_active ? '<span class="on">On</span>' : '<span class="off">Off</span>' !!}</td>
                <td class="actions">
                    <a href="{{ route('admin.learning.edit', $v) }}">Edit</a>
                    <form method="post" action="{{ route('admin.learning.destroy', $v) }}" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')<button class="link danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6">No videos yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $videos->links('admin.pagination') }}
@endsection
