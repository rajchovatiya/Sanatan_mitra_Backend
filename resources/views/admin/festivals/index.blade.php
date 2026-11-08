@extends('admin.layout')
@section('title', 'Festivals & Vrat')
@section('content')
    <div class="head">
        <h1>Festivals &amp; Vrat · {{ $month->format('F Y') }}</h1>
        <a class="btn" href="{{ route('admin.festivals.create', ['date' => $month->toDateString()]) }}">+ Add festival</a>
    </div>
    <p class="tabs">
        <a href="{{ route('admin.festivals.index', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}">‹ {{ $month->copy()->subMonth()->format('M Y') }}</a>
        <a href="{{ route('admin.festivals.index') }}">This month</a>
        <a href="{{ route('admin.festivals.index', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}">{{ $month->copy()->addMonth()->format('M Y') }} ›</a>
    </p>
    <table>
        <thead><tr><th></th><th>Date</th><th>Name</th><th>Tithi</th><th>Type</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($festivals as $f)
            <tr>
                <td>@if ($f->image_path)<img class="thumb" src="{{ \App\Support\Media::url($f->image_path) }}" alt="">@endif</td>
                <td>{{ $f->date->format('d M, D') }}</td>
                <td>{{ $f->name['en'] ?? '' }}<br><small>{{ $f->name['hi'] ?? '' }}</small></td>
                <td>{{ $f->tithi['en'] ?? '' }}</td>
                <td>{{ $f->is_vrat ? 'Vrat' : 'Festival' }}</td>
                <td>{!! $f->is_active ? '<span class="on">On</span>' : '<span class="off">Off</span>' !!}</td>
                <td class="actions">
                    <a href="{{ route('admin.festivals.edit', $f) }}">Edit</a>
                    <form method="post" action="{{ route('admin.festivals.destroy', $f) }}" onsubmit="return confirm('Delete this festival?')">
                        @csrf @method('DELETE')<button class="link danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7">Nothing added for this month yet.</td></tr>
        @endforelse
        </tbody>
    </table>
@endsection
