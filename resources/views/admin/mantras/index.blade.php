@extends('admin.layout')
@section('title', 'Mantras')
@section('content')
    <div class="head">
        <h1>Mantras</h1>
        <a class="btn" href="{{ route('admin.mantras.create') }}">+ Add mantra</a>
    </div>
    @include('admin.partials.category-filter', ['route' => 'admin.mantras.index'])
    <table>
        <thead><tr><th></th><th>Title</th><th>Deity</th><th>Category</th><th>Audio</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($mantras as $m)
            <tr>
                <td>@if ($m->image_path)<img class="thumb round" src="{{ \App\Support\Media::url($m->image_path) }}" alt="">@endif</td>
                <td>{{ $m->title['en'] ?? '' }}<br><small>{{ $m->title['hi'] ?? '' }}</small></td>
                <td>{{ $m->deity['en'] ?? '' }}</td>
                <td>{{ $m->category->name['en'] ?? '' }}</td>
                <td>@if ($m->audio_path)<audio controls preload="none" src="{{ \App\Support\Media::url($m->audio_path) }}"></audio>@else <span class="off">None</span> @endif</td>
                <td>{!! $m->is_active ? '<span class="on">On</span>' : '<span class="off">Off</span>' !!}</td>
                <td class="actions">
                    <a href="{{ route('admin.mantras.edit', $m) }}">Edit</a>
                    <form method="post" action="{{ route('admin.mantras.destroy', $m) }}" onsubmit="return confirm('Delete this mantra?')">
                        @csrf @method('DELETE')<button class="link danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7">No mantras yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $mantras->links('admin.pagination') }}
@endsection
