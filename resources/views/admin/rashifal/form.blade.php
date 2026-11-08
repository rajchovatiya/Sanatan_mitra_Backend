@extends('admin.layout')
@section('title', 'Rashifal')
@section('content')
    <h1>{{ $rashifal->exists ? 'Edit' : 'Write' }} rashifal</h1>
    <form method="post" class="card"
          action="{{ $rashifal->exists ? route('admin.rashifal.update', $rashifal) : route('admin.rashifal.store') }}">
        @csrf
        @if ($rashifal->exists) @method('PUT') @endif

        <div class="row">
            <label>Date *
                <input type="date" name="date" value="{{ old('date', $rashifal->date?->format('Y-m-d')) }}" required>
            </label>
            <label>Rashi *
                <select name="rashi">
                    @foreach (\App\Models\Rashifal::RASHIS as $i => $name)
                        <option value="{{ $i }}" @selected((int) old('rashi', $rashifal->rashi) === $i)>{{ $name }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        @foreach (\App\Models\Rashifal::AREAS as $area)
            <x-admin.lang-input :name="$area" :label="ucfirst($area)" :value="$rashifal->{$area} ?? []" textarea :required="$area === 'overall'" />
            <label>{{ ucfirst($area) }} stars (1–5)
                <input type="number" name="stars[{{ $area }}]" min="1" max="5" value="{{ old("stars.{$area}", $rashifal->stars[$area] ?? '') }}">
            </label>
        @endforeach

        <div class="row">
            <label>Lucky number <input type="number" name="lucky_number" min="1" max="99" value="{{ old('lucky_number', $rashifal->lucky_number) }}"></label>
            <label>Lucky time (e.g. 10:00–11:30) <input type="text" name="lucky_time" value="{{ old('lucky_time', $rashifal->lucky_time) }}"></label>
        </div>
        <x-admin.lang-input name="lucky_colour" label="Lucky colour" :value="$rashifal->lucky_colour ?? []" :required="false" />
        <x-admin.lang-input name="lucky_direction" label="Lucky direction" :value="$rashifal->lucky_direction ?? []" :required="false" />

        <button class="btn">Save</button>
        <a href="{{ route('admin.rashifal.index', ['date' => $rashifal->date?->format('Y-m-d')]) }}">Cancel</a>
    </form>
@endsection
