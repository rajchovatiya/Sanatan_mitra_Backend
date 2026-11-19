@extends('admin.layout')
@section('title', 'App user')
@section('content')
    <div class="head">
        <h1>{{ $user->name ?: 'Unnamed user' }} · {{ $user->mobile }}</h1>
        <form method="post" action="{{ route('admin.users.block', $user) }}"
              onsubmit="return confirm('{{ $user->isBlocked() ? 'Unblock this user?' : 'Block this user? They will be logged out on all phones.' }}')">
            @csrf @method('PATCH')
            <button class="btn">{{ $user->isBlocked() ? 'Unblock' : 'Block' }}</button>
        </form>
        @can('manage-admins')
            <form method="post" action="{{ route('admin.users.destroy', $user) }}"
                  onsubmit="return confirm('Permanently delete this account and all its data? This cannot be undone.')">
                @csrf @method('DELETE')
                <button class="btn danger">Delete account</button>
            </form>
        @endcan
    </div>
    <p><a href="{{ route('admin.users.index') }}">‹ All users</a></p>
    <div class="card">
        <table>
            <tbody>
            <tr><th>Status</th><td>{!! $user->isBlocked() ? '<span class="danger">Blocked</span>' : '<span class="on">Active</span>' !!}</td></tr>
            <tr><th>City</th><td>{{ $user->city }}</td></tr>
            <tr><th>Language</th><td>{{ strtoupper($user->language) }}</td></tr>
            <tr><th>Rashi</th><td>{{ $user->rashi !== null ? (\App\Models\Rashifal::RASHIS[$user->rashi] ?? '') : '' }}</td></tr>
            <tr><th>Birth</th><td>{{ $user->birth_date?->format('d M Y') }} {{ $user->birth_time ? substr($user->birth_time, 0, 5) : '' }} {{ $user->birth_place }}</td></tr>
            <tr><th>Business on posters</th><td>{{ $user->business_name }}</td></tr>
            <tr><th>Saved kundlis / favourites / phones</th><td>{{ $user->kundlis_count }} / {{ $user->favorites_count }} / {{ $user->devices_count }}</td></tr>
            <tr><th>Joined</th><td>{{ $user->created_at->format('d M Y, H:i') }}</td></tr>
            <tr><th>Last login</th><td>{{ $user->last_login_at?->format('d M Y, H:i') }}</td></tr>
            </tbody>
        </table>
    </div>
@endsection
