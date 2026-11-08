@extends('admin.layout')
@section('title', 'Temples')
@section('content')
    <div class="head">
        <h1>Temples</h1>
        <a class="btn" href="{{ route('admin.temples.create') }}">+ Add temple</a>
    </div>
    <p class="tabs">
        <a href="{{ route('admin.temples.index') }}" @class(['active' => ! $type])>All</a>
        @foreach (\App\Models\Temple::TYPES as $key => $label)
            <a href="{{ route('admin.temples.index', ['type' => $key]) }}" @class(['active' => $type === $key])>{{ $label }}</a>
        @endforeach
    </p>
    <table>
        <thead><tr><th></th><th>Name</th><th>Type</th><th>State</th><th>Order</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($temples as $t)
            <tr>
                <td>@if ($t->image_path)<img class="thumb" src="{{ \App\Support\Media::url($t->image_path) }}" alt="">@endif</td>
                <td>{{ $t->name['en'] ?? '' }}<br><small>{{ $t->name['hi'] ?? '' }}</small></td>
                <td>{{ \App\Models\Temple::TYPES[$t->type] ?? $t->type }}</td>
                <td>{{ $t->state }}</td>
                <td>{{ $t->sort_order }}</td>
                <td>{!! $t->is_active ? '<span class="on">On</span>' : '<span class="off">Off</span>' !!}</td>
                <td class="actions">
                    <a href="{{ route('admin.temples.edit', $t) }}">Edit</a>
                    <form method="post" action="{{ route('admin.temples.destroy', $t) }}" onsubmit="return confirm('Delete this temple?')">
                        @csrf @method('DELETE')<button class="link danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7">No temples yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $temples->links('admin.pagination') }}
@endsection
