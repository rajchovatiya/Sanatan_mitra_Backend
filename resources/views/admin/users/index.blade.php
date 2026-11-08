@extends('admin.layout')
@section('title', 'App users')
@section('content')
    <div class="head">
        <h1>App users</h1>
        <form method="get" class="actions">
            <input type="text" name="q" value="{{ $q }}" placeholder="Mobile or name">
            <button class="btn">Search</button>
        </form>
    </div>
    <table>
        <thead><tr><th>Mobile</th><th>Name</th><th>City</th><th>Language</th><th>Joined</th><th>Last login</th><th>Status</th></tr></thead>
        <tbody>
        @forelse ($users as $u)
            <tr>
                <td><a href="{{ route('admin.users.show', $u) }}">{{ $u->mobile }}</a></td>
                <td>{{ $u->name }}</td>
                <td>{{ $u->city }}</td>
                <td>{{ strtoupper($u->language) }}</td>
                <td>{{ $u->created_at->format('d M Y') }}</td>
                <td>{{ $u->last_login_at?->diffForHumans() }}</td>
                <td>{!! $u->isBlocked() ? '<span class="danger">Blocked</span>' : '<span class="on">Active</span>' !!}</td>
            </tr>
        @empty
            <tr><td colspan="7">No users found.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $users->links('admin.pagination') }}
@endsection
