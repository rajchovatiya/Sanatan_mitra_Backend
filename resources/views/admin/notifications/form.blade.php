@extends('admin.layout')
@section('title', 'Send notification')
@section('content')
    <h1>Send notification</h1>
    <form method="post" enctype="multipart/form-data" class="card" action="{{ route('admin.notifications.store') }}"
          onsubmit="return confirm('Send this to all app users now?')">
        @csrf
        <x-admin.lang-input name="title" label="Title (max 80)" />
        <x-admin.lang-input name="body" label="Message (max 300)" textarea />
        <label>Open on tap (optional): app path like /festivals/5, or an https:// link
            <input type="text" name="link" value="{{ old('link') }}">
        </label>
        <label>Image (optional, jpg/png, max 1 MB) <input type="file" name="image" accept="image/png,image/jpeg"></label>
        <button class="btn">Send to all users</button>
        <a href="{{ route('admin.notifications.index') }}">Cancel</a>
    </form>
@endsection
