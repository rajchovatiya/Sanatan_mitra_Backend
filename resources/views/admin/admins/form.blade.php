@extends('admin.layout')
@section('title', 'Admin')
@section('content')
    <h1>{{ $admin->exists ? 'Edit admin' : 'Add admin' }}</h1>
    <form method="post" class="card" action="{{ $admin->exists ? route('admin.admins.update', $admin) : route('admin.admins.store') }}">
        @csrf
        @if ($admin->exists) @method('PUT') @endif
        <div class="row">
            <label>Name * <input type="text" name="name" value="{{ old('name', $admin->name) }}" required></label>
            <label>Email * <input type="email" name="email" value="{{ old('email', $admin->email) }}" required></label>
        </div>
        <div class="row">
            <label>Role
                <select name="role">
                    <option value="editor" @selected(old('role', $admin->role) === 'editor')>Editor (content and users)</option>
                    <option value="super_admin" @selected(old('role', $admin->role) === 'super_admin')>Super admin (everything)</option>
                </select>
            </label>
            <label>Password {{ $admin->exists ? '(leave empty to keep)' : '*' }} – at least 10 characters
                <input type="password" name="password" autocomplete="new-password" @required(! $admin->exists)>
            </label>
        </div>
        <label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $admin->exists ? $admin->is_active : true))> Can log in</label>
        <button class="btn">Save</button>
        <a href="{{ route('admin.admins.index') }}">Cancel</a>
    </form>
@endsection
