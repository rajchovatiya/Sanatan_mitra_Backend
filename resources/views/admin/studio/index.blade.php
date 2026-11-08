@extends('admin.layout')
@section('title', 'Wallpapers & Videos')
@section('content')
    <div class="head">
        <h1>{{ \App\Models\StudioMedia::KINDS[$kind] }}</h1>
        <a class="btn" href="{{ route('admin.studio.create', ['kind' => $kind]) }}">+ Upload</a>
    </div>
    <p class="tabs">
        @foreach (\App\Models\StudioMedia::KINDS as $key => $label)
            <a href="{{ route('admin.studio.index', ['kind' => $key]) }}" @class(['active' => $kind === $key])>{{ $label }}</a>
        @endforeach
        <a href="{{ route('admin.categories.index', ['type' => $kind]) }}">Manage categories</a>
    </p>
    <div class="grid">
        @forelse ($items as $m)
            <div class="card tile">
                <img src="{{ \App\Support\Media::url($m->image_path) }}" alt="" loading="lazy">
                <strong>{{ $m->title['en'] ?? '' }}</strong>
                <small>{{ $m->category->name['en'] ?? '' }} · {!! $m->is_active ? '<span class="on">On</span>' : '<span class="off">Off</span>' !!}</small>
                <div class="actions">
                    <a href="{{ route('admin.studio.edit', $m) }}">Edit</a>
                    <form method="post" action="{{ route('admin.studio.destroy', $m) }}" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')<button class="link danger">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <p>Nothing uploaded yet.</p>
        @endforelse
    </div>
    {{ $items->links('admin.pagination') }}
@endsection
