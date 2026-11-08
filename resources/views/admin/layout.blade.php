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
            <a href="{{ route('admin.festivals.index') }}" @class(['active' => request()->routeIs('admin.festivals.*')])>Festivals</a>
            <a href="{{ route('admin.rashifal.index') }}" @class(['active' => request()->routeIs('admin.rashifal.*')])>Rashifal</a>
            <a href="{{ route('admin.temples.index') }}" @class(['active' => request()->routeIs('admin.temples.*')])>Temples</a>
            <a href="{{ route('admin.darshans.index') }}" @class(['active' => request()->routeIs('admin.darshans.*')])>Darshan</a>
            <a href="{{ route('admin.library.index') }}" @class(['active' => request()->routeIs('admin.library.*')])>Katha &amp; Aarti</a>
            <a href="{{ route('admin.gita.chapters') }}" @class(['active' => request()->routeIs('admin.gita*')])>Gita</a>
            <a href="{{ route('admin.suvichars.index') }}" @class(['active' => request()->routeIs('admin.suvichars.*')])>Suvichar</a>
            <a href="{{ route('admin.muhurat.index') }}" @class(['active' => request()->routeIs('admin.muhurat.*')])>Muhurat</a>
            <a href="{{ route('admin.guides.index') }}" @class(['active' => request()->routeIs('admin.guides.*')])>Guides</a>
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
