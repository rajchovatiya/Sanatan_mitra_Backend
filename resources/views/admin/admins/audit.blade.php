@extends('admin.layout')
@section('title', 'Audit log')
@section('content')
    <h1>Audit log</h1>
    <p class="tabs">
        <a href="{{ route('admin.audit.index') }}" @class(['active' => ! request('admin')])>All admins</a>
        @foreach ($admins as $a)
            <a href="{{ route('admin.audit.index', ['admin' => $a->id]) }}" @class(['active' => (int) request('admin') === $a->id])>{{ $a->name }}</a>
        @endforeach
    </p>
    <table>
        <thead><tr><th>When</th><th>Admin</th><th>Action</th><th>On</th><th>IP</th></tr></thead>
        <tbody>
        @forelse ($logs as $l)
            <tr>
                <td>{{ $l->created_at->format('d M Y, H:i') }}</td>
                <td>{{ $l->admin?->name ?? 'Removed admin' }}</td>
                <td>{{ str_replace('admin.', '', $l->action) }}</td>
                <td>{{ $l->subject }}</td>
                <td><small>{{ $l->ip }}</small></td>
            </tr>
        @empty
            <tr><td colspan="5">No changes recorded yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    {{ $logs->links('admin.pagination') }}
@endsection
