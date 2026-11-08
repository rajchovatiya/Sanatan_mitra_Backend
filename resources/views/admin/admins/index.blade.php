@extends('admin.layout')
@section('title', 'Admins')
@section('content')
    <div class="head">
        <h1>Admins</h1>
        <a class="btn" href="{{ route('admin.admins.create') }}">+ Add admin</a>
    </div>
    <p><small>Super admins manage everything. Editors manage content and users, but not settings, admins or the audit log.</small></p>
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Last login</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach ($admins as $a)
            <tr>
                <td>{{ $a->name }}</td>
                <td>{{ $a->email }}</td>
                <td>{{ $a->isSuper() ? 'Super admin' : 'Editor' }}</td>
                <td>{{ $a->last_login_at?->diffForHumans() }}</td>
                <td>{!! $a->is_active ? '<span class="on">Active</span>' : '<span class="off">Disabled</span>' !!}</td>
                <td class="actions"><a href="{{ route('admin.admins.edit', $a) }}">Edit</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
