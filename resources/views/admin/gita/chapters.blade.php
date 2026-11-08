@extends('admin.layout')
@section('title', 'Bhagavad Gita')
@section('content')
    <div class="head">
        <h1>Bhagavad Gita</h1>
        <a class="btn" href="{{ route('admin.gita-verses.create') }}">+ Add verse</a>
    </div>
    <table>
        <thead><tr><th>#</th><th>Chapter</th><th>Verses added</th><th></th></tr></thead>
        <tbody>
        @foreach ($chapters as $c)
            <tr>
                <td>{{ $c->number }}</td>
                <td>{{ $c->name['en'] ?? '' }}<br><small>{{ $c->name['hi'] ?? '' }}</small></td>
                <td>{{ $c->verses_count }} / {{ $c->verse_count }}</td>
                <td class="actions">
                    <a href="{{ route('admin.gita-verses.index', ['chapter' => $c->number]) }}">Verses</a>
                    <a href="{{ route('admin.gita.chapters.edit', $c) }}">Edit name</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
