@extends('admin.layout')
@section('title', 'Rashifal')
@section('content')
    @php($day = \Illuminate\Support\Carbon::parse($date))
    <div class="head">
        <h1>Rashifal · {{ $day->format('d M Y, l') }}</h1>
        <form method="get" class="actions">
            <input type="date" name="date" value="{{ $date }}">
            <button class="btn">Go</button>
        </form>
    </div>
    <p class="tabs">
        <a href="{{ route('admin.rashifal.index', ['date' => $day->copy()->subDay()->toDateString()]) }}">‹ Previous day</a>
        <a href="{{ route('admin.rashifal.index') }}">Today</a>
        <a href="{{ route('admin.rashifal.index', ['date' => $day->copy()->addDay()->toDateString()]) }}">Next day ›</a>
    </p>
    <p><strong>{{ $written->count() }} / 12</strong> written. Rashis not written show the app's built-in text.</p>
    <table>
        <thead><tr><th>Rashi</th><th>Overall</th><th></th></tr></thead>
        <tbody>
        @foreach (\App\Models\Rashifal::RASHIS as $i => $name)
            @php($r = $written->get($i))
            <tr>
                <td><strong>{{ $name }}</strong></td>
                <td>{!! $r ? e(\Illuminate\Support\Str::limit($r->overall['en'] ?? '', 90)) : '<span class="off">Not written</span>' !!}</td>
                <td class="actions">
                    @if ($r)
                        <a href="{{ route('admin.rashifal.edit', $r) }}">Edit</a>
                        <form method="post" action="{{ route('admin.rashifal.destroy', $r) }}" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')<button class="link danger">Delete</button>
                        </form>
                    @else
                        <a class="btn" href="{{ route('admin.rashifal.create', ['date' => $date, 'rashi' => $i]) }}">Write</a>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
