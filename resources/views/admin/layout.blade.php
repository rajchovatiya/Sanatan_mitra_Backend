<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
</head>
<body>
@auth('admin')
    <header class="topbar">
        <a href="{{ route('admin.dashboard') }}" class="brand">🪔 {{ config('app.name') }} Admin</a>
        <nav>
            <a href="{{ route('admin.categories.index') }}" @class(['active' => request()->routeIs('admin.categories.*')])>Categories</a>
            <a href="{{ route('admin.posters.index') }}" @class(['active' => request()->routeIs('admin.posters.*')])>Posters</a>
            <a href="{{ route('admin.mantras.index') }}" @class(['active' => request()->routeIs('admin.mantras.*')])>Mantras</a>
        </nav>
        <form method="post" action="{{ route('admin.logout') }}">
            @csrf
            <button class="link">Logout ({{ auth('admin')->user()->name }})</button>
        </form>
    </header>
@endauth
<main class="page">
    @if (session('status'))
        <p class="flash">{{ session('status') }}</p>
    @endif
    @if ($errors->any())
        <div class="flash error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif
    @yield('content')
</main>
</body>
</html>
