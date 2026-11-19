<!DOCTYPE html>
<html lang="{{ $lang ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · Sanatan Shaily</title>
    <style>
        :root { --saffron: #e65100; --ink: #2b1b0e; --muted: #6d5b4b; --line: #f0dcc0; --bg: #fffaf2; }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--bg); color: var(--ink); font: 16px/1.65 system-ui, -apple-system, "Segoe UI", Roboto, "Noto Sans", sans-serif; }
        header { background: linear-gradient(135deg, #ffa634, #e65100); color: #fff; padding: 22px 16px; }
        header .wrap { max-width: 760px; margin: 0 auto; }
        header .brand { font-weight: 800; letter-spacing: .3px; opacity: .95; }
        header h1 { margin: 4px 0 0; font-size: 26px; line-height: 1.25; }
        main { max-width: 760px; margin: 0 auto; padding: 20px 16px 48px; }
        h2 { font-size: 19px; margin: 28px 0 6px; color: var(--saffron); }
        p, li { margin: 6px 0; }
        ul { padding-left: 22px; }
        .muted { color: var(--muted); font-size: 14px; }
        .card { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 18px; margin: 16px 0; }
        .ok { background: #edf7ed; border-color: #b7dfb9; }
        label { display: block; font-weight: 700; margin: 12px 0 4px; }
        input, textarea { width: 100%; font: inherit; padding: 10px 12px; border: 1px solid #d9c3a3; border-radius: 10px; background: #fff; }
        button { margin-top: 14px; background: var(--saffron); color: #fff; border: 0; border-radius: 10px; padding: 11px 18px; font: inherit; font-weight: 700; cursor: pointer; }
        .error { color: #b3261e; font-size: 14px; }
        .langs a { color: #fff; margin-right: 10px; font-size: 14px; }
        .body { white-space: pre-line; }
        footer { max-width: 760px; margin: 0 auto; padding: 0 16px 32px; }
        footer a { color: var(--saffron); margin-right: 14px; }
    </style>
</head>
<body>
<header>
    <div class="wrap">
        <div class="brand">Sanatan Shaily</div>
        <h1>@yield('title')</h1>
        @yield('langs')
    </div>
</header>
<main>
    @yield('content')
</main>
<footer class="muted">
    <a href="{{ route('public.privacy') }}">Privacy Policy</a>
    <a href="{{ route('public.terms') }}">Terms</a>
    <a href="{{ route('public.delete-account') }}">Delete account</a>
</footer>
</body>
</html>
