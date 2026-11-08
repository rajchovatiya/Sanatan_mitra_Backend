@extends('admin.layout')
@section('title', 'Muhurat dates')
@section('content')
    <div class="head">
        <h1>Muhurat dates · {{ $year }}</h1>
        <a class="btn" href="{{ route('admin.muhurat.create') }}">+ Add date</a>
    </div>
    <p><small>Muhurat types (Vivah, Griha Pravesh…) are managed in <a href="{{ route('admin.categories.index', ['type' => 'muhurat']) }}">Categories</a>.</small></p>
    <p class="tabs">
        <a href="{{ route('admin.muhurat.index', ['year' => $year - 1, 'category' => $category]) }}">‹ {{ $year - 1 }}</a>
        <a href="{{ route('admin.muhurat.index', ['year' => $year]) }}" @class(['active' => ! $category])>All types</a>
        @foreach ($categories as $c)
            <a href="{{ route('admin.muhurat.index', ['year' => $year, 'category' => $c->id]) }}" @class(['active' => $category === $c->id])>{{ $c->name['en'] ?? '' }}</a>
        @endforeach
        <a href="{{ route('admin.muhurat.index', ['year' => $year + 1, 'category' => $category]) }}">{{ $year + 1 }} ›</a>
    </p>
    <table>
        <thead><tr><th>Date</th><th>Type</th><th>Time</th><th>Note</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($dates as $d)
            <tr>
                <td>{{ $d->date->format('d M Y, D') }}</td>
                <td>{{ $d->category->name['en'] ?? '' }}</td>
                <td>{{ $d->start_time ? substr($d->start_time, 0, 5) : '' }}{{ $d->end_time ? ' – '.substr($d->end_time, 0, 5) : '' }}</td>
                <td>{{ $d->note['en'] ?? '' }}</td>
                <td>{!! $d->is_active ? '<span class="on">On</span>' : '<span class="off">Off</span>' !!}</td>
                <td class="actions">
                    <a href="{{ route('admin.muhurat.edit', $d) }}">Edit</a>
                    <form method="post" action="{{ route('admin.muhurat.destroy', $d) }}" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')<button class="link danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6">No dates for this year yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $dates->links('admin.pagination') }}
@endsection
