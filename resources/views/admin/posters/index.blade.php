@extends('admin.layout')
@section('title', 'Posters')
@section('content')
    <div class="head">
        <h1>Posters</h1>
        <span>
            <a class="btn" href="{{ route('admin.posters.bulk', ['category' => $category]) }}">+ Upload many (up to 10)</a>
            <a class="btn" href="{{ route('admin.posters.create') }}">+ Upload poster</a>
        </span>
    </div>
    @include('admin.partials.category-filter', ['route' => 'admin.posters.index'])
    <div class="grid">
        @forelse ($posters as $p)
            <div class="card tile">
                <img src="{{ \App\Support\Media::url($p->image_path) }}" alt="" loading="lazy">
                <strong>{{ $p->title['en'] ?? '' }}</strong>
                <small>{{ $p->category->name['en'] ?? '' }} · {{ $p->frame }} · {!! $p->is_active ? '<span class="on">On</span>' : '<span class="off">Off</span>' !!}</small>
                <div class="actions">
                    <a href="{{ route('admin.posters.edit', $p) }}">Edit</a>
                    <form method="post" action="{{ route('admin.posters.destroy', $p) }}" onsubmit="return confirm('Delete this poster?')">
                        @csrf @method('DELETE')<button class="link danger">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <p>No posters yet.</p>
        @endforelse
    </div>
    {{ $posters->links('admin.pagination') }}
@endsection
