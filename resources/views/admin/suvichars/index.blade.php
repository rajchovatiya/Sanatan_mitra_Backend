@extends('admin.layout')
@section('title', 'Suvichar')
@section('content')
    <div class="head">
        <h1>Suvichar</h1>
        <a class="btn" href="{{ route('admin.suvichars.create') }}">+ Add suvichar</a>
    </div>
    <p><small>A suvichar with a date shows on that day. Without a date, they take turns, one per day.</small></p>
    <table>
        <thead><tr><th>Date</th><th>Text</th><th>Author</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($suvichars as $s)
            <tr>
                <td>{{ $s->date?->format('d M Y') ?? 'Rotating' }}</td>
                <td>{{ \Illuminate\Support\Str::limit($s->text['en'] ?? '', 90) }}</td>
                <td>{{ $s->author['en'] ?? '' }}</td>
                <td>{!! $s->is_active ? '<span class="on">On</span>' : '<span class="off">Off</span>' !!}</td>
                <td class="actions">
                    <a href="{{ route('admin.suvichars.edit', $s) }}">Edit</a>
                    <form method="post" action="{{ route('admin.suvichars.destroy', $s) }}" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')<button class="link danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5">No suvichar yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $suvichars->links('admin.pagination') }}
@endsection
