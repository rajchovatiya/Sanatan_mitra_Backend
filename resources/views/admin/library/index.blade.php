@extends('admin.layout')
@section('title', 'Katha & Aarti')
@section('content')
    <div class="head">
        <h1>Katha, Aarti, Beej Mantra, Chalisa</h1>
        <a class="btn" href="{{ route('admin.library.create', ['type' => $type ?? 'katha']) }}">+ Add</a>
    </div>
    <p class="tabs">
        <a href="{{ route('admin.library.index') }}" @class(['active' => ! $type])>All</a>
        @foreach (\App\Models\LibraryItem::TYPES as $key => $label)
            <a href="{{ route('admin.library.index', ['type' => $key]) }}" @class(['active' => $type === $key])>{{ $label }}</a>
        @endforeach
    </p>
    <table>
        <thead><tr><th>Title</th><th>Type</th><th>Audio</th><th>Order</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($items as $i)
            <tr>
                <td>{{ $i->title['en'] ?? '' }}<br><small>{{ $i->title['hi'] ?? '' }}</small></td>
                <td>{{ \App\Models\LibraryItem::TYPES[$i->type] ?? $i->type }}</td>
                <td>{{ $i->audio_path ? 'Yes' : '' }}</td>
                <td>{{ $i->sort_order }}</td>
                <td>{!! $i->is_active ? '<span class="on">On</span>' : '<span class="off">Off</span>' !!}</td>
                <td class="actions">
                    <a href="{{ route('admin.library.edit', $i) }}">Edit</a>
                    <form method="post" action="{{ route('admin.library.destroy', $i) }}" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')<button class="link danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6">Nothing added yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $items->links('admin.pagination') }}
@endsection
