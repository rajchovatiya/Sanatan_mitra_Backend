@extends('admin.layout')
@section('title', 'Categories')
@section('content')
    <div class="head">
        <h1>Categories</h1>
        <a class="btn" href="{{ route('admin.categories.create', ['type' => $type ?? 'poster']) }}">+ Add category</a>
    </div>
    <p class="tabs">
        <a href="{{ route('admin.categories.index') }}" @class(['active' => ! $type])>All</a>
        @foreach (\App\Models\Category::TYPES as $key => $label)
            <a href="{{ route('admin.categories.index', ['type' => $key]) }}" @class(['active' => $type === $key])>{{ $label }}</a>
        @endforeach
    </p>
    <table>
        <thead><tr><th></th><th>Name</th><th>Module</th><th>Items</th><th>Order</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($categories as $c)
            <tr>
                <td>@if ($c->icon_path)<img class="thumb" src="{{ \App\Support\Media::url($c->icon_path) }}" alt="">@endif</td>
                <td>{{ $c->name['en'] ?? '' }}<br><small>{{ $c->name['hi'] ?? '' }}</small></td>
                <td>{{ \App\Models\Category::TYPES[$c->type] ?? $c->type }}</td>
                <td>{{ $c->posters_count + $c->mantras_count + $c->muhurat_dates_count + $c->studio_media_count + $c->learning_videos_count }}</td>
                <td>{{ $c->sort_order }}</td>
                <td>{!! $c->is_active ? '<span class="on">On</span>' : '<span class="off">Off</span>' !!}</td>
                <td class="actions">
                    <a href="{{ route('admin.categories.edit', $c) }}">Edit</a>
                    <form method="post" action="{{ route('admin.categories.destroy', $c) }}" onsubmit="return confirm('Delete this category?')">
                        @csrf @method('DELETE')<button class="link danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7">No categories yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $categories->links('admin.pagination') }}
@endsection
