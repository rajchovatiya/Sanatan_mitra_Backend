@extends('admin.layout')
@section('title', 'Login')
@section('content')
    <form method="post" action="{{ route('admin.login.store') }}" class="card narrow">
        @csrf
        <h1>🪔 Admin login</h1>
        <label>Email <input type="email" name="email" value="{{ old('email') }}" required autofocus></label>
        <label>Password <input type="password" name="password" required></label>
        <label class="check"><input type="checkbox" name="remember" value="1"> Keep me logged in</label>
        <button class="btn">Login</button>
    </form>
@endsection
