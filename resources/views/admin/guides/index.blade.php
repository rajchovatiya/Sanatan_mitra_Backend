@extends('admin.layout')
@section('title', 'Guides')
@section('content')
    <div class="head">
        <h1>{{ \App\Models\Guide::MODULES[$module] }}</h1>
        <a class="btn" href="{{ route('admin.guides.create', ['module' => $module]) }}">+ Add</a>
    </div>
    <p class="tabs">
        @foreach (\App\Models\Guide::MODULES as $key => $label)
            <a href="{{ route('admin.guides.index', ['module' => $key]) }}" @class(['active' => $module === $key])>{{ $label }}</a>
        @endforeach
    </p>
    <table>
        <thead><tr><th></th><th>Title</th><th>Subtitle</th><th>Order</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($guides as $g)
            <tr>
                <td>@if ($g->image_path)<img class="thumb" src="{{ \App\Support\Media::url($g->image_path) }}" alt="">@endif</td>
                <td>{{ $g->title['en'] ?? '' }}<br><small>{{ $g->title['hi'] ?? '' }}</small></td>
                <td>{{ $g->subtitle['en'] ?? '' }}</td>
                <td>{{ $g->sort_order }}</td>
                <td>{!! $g->is_active ? '<span class="on">On</span>' : '<span class="off">Off</span>' !!}</td>
                <td class="actions">
                    <a href="{{ route('admin.guides.edit', $g) }}">Edit</a>
                    <form method="post" action="{{ route('admin.guides.destroy', $g) }}" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')<button class="link danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6">Nothing added yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $guides->links('admin.pagination') }}
@endsection
