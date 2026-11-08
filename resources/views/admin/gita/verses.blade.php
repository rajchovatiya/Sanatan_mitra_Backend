@extends('admin.layout')
@section('title', 'Gita verses')
@section('content')
    <div class="head">
        <h1>Chapter {{ $chapter->number }}: {{ $chapter->name['en'] ?? '' }}</h1>
        <a class="btn" href="{{ route('admin.gita-verses.create', ['chapter' => $chapter->number]) }}">+ Add verse</a>
    </div>
    <p><a href="{{ route('admin.gita.chapters') }}">‹ All chapters</a></p>
    <table>
        <thead><tr><th>Verse</th><th>Sanskrit</th><th>Meaning</th><th>Daily</th><th></th></tr></thead>
        <tbody>
        @forelse ($verses as $v)
            <tr>
                <td>{{ $v->chapter }}.{{ $v->verse }}</td>
                <td>{{ \Illuminate\Support\Str::limit($v->sanskrit, 60) }}</td>
                <td>{{ \Illuminate\Support\Str::limit($v->meaning['en'] ?? '', 70) }}</td>
                <td>{!! $v->is_featured ? '<span class="on">★</span>' : '' !!}</td>
                <td class="actions">
                    <a href="{{ route('admin.gita-verses.edit', $v) }}">Edit</a>
                    <form method="post" action="{{ route('admin.gita-verses.destroy', $v) }}" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')<button class="link danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5">No verses added for this chapter yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $verses->links('admin.pagination') }}
@endsection
