@extends('admin.layout')
@section('title', 'Pages')
@section('content')
    <h1>App pages</h1>
    <table>
        <thead><tr><th>Page</th><th>Last updated</th><th></th></tr></thead>
        <tbody>
        @foreach ($pages as $p)
            <tr>
                <td>{{ $p->title['en'] ?? $p->slug }}<br><small>/pages/{{ $p->slug }}</small></td>
                <td>{{ $p->updated_at?->format('d M Y') }}</td>
                <td class="actions"><a href="{{ route('admin.pages.edit', $p) }}">Edit</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
