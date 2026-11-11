<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
    @auth('admin')
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="translate-url" content="{{ route('admin.translate') }}">
        <script src="{{ asset('assets/js/admin-translate.js') }}" defer></script>
    @endauth
</head>
<body>
@auth('admin')
    @php
        $menu = [
            'Daily' => [
                'admin.rashifal' => 'Rashifal', 'admin.festivals' => 'Festivals & Vrat', 'admin.darshans' => 'Daily Darshan',
                'admin.suvichars' => 'Suvichar', 'admin.muhurat' => 'Muhurat',
            ],
            'Content' => [
                'admin.posters' => 'Posters', 'admin.studio' => 'Wallpapers & Videos', 'admin.mantras' => 'Mantras',
                'admin.library' => 'Katha & Aarti', 'admin.gita' => 'Bhagavad Gita', 'admin.temples' => 'Temples',
                'admin.guides' => 'Guides', 'admin.learning' => 'Live & Courses', 'admin.categories' => 'Categories',
            ],
            'Users' => [
                'admin.users' => 'App users', 'admin.notifications' => 'Notifications', 'admin.feedback' => 'Feedback', 'admin.pages' => 'Pages',
            ],
        ];
        if (auth('admin')->user()->isSuper()) {
            $menu['Admin'] = ['admin.settings' => 'App settings', 'admin.admins' => 'Admins', 'admin.audit' => 'Audit log'];
        }
        // Each menu item opens the module's main page.
        $target = fn ($prefix) => match ($prefix) {
            'admin.gita' => route('admin.gita.chapters'),
            'admin.settings' => route('admin.settings.edit'),
            default => route($prefix.'.index'),
        };
    @endphp
    <div class="shell">
        <aside class="sidebar">
            <a href="{{ route('admin.dashboard') }}" class="brand">🪔 {{ config('app.name') }}</a>
            <a href="{{ route('admin.dashboard') }}" @class(['active' => request()->routeIs('admin.dashboard')])>Dashboard</a>
            @foreach ($menu as $group => $items)
                <p class="group">{{ $group }}</p>
                @foreach ($items as $prefix => $label)
                    <a href="{{ $target($prefix) }}" @class(['active' => request()->routeIs($prefix.'*')])>{{ $label }}</a>
                @endforeach
            @endforeach
            <form method="post" action="{{ route('admin.logout') }}" class="logout">
                @csrf
                <button class="link">Logout ({{ auth('admin')->user()->name }})</button>
            </form>
        </aside>
        <main class="page">
            @include('admin.partials.flash')
            @yield('content')
        </main>
    </div>
@else
    <main class="page">
        @include('admin.partials.flash')
        @yield('content')
    </main>
@endauth
</body>
</html>
