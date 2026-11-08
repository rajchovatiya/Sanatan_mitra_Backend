@extends('admin.layout')
@section('title', 'Muhurat date')
@section('content')
    <h1>{{ $muhurat->exists ? 'Edit' : 'Add' }} muhurat date</h1>
    <form method="post" class="card"
          action="{{ $muhurat->exists ? route('admin.muhurat.update', $muhurat) : route('admin.muhurat.store') }}">
        @csrf
        @if ($muhurat->exists) @method('PUT') @endif
        @include('admin.partials.category-select', ['selected' => $muhurat->category_id])
        <div class="row">
            <label>Date * <input type="date" name="date" value="{{ old('date', $muhurat->date?->format('Y-m-d')) }}" required></label>
            <label>From (optional) <input type="time" name="start_time" value="{{ old('start_time', $muhurat->start_time ? substr($muhurat->start_time, 0, 5) : '') }}"></label>
            <label>To (optional) <input type="time" name="end_time" value="{{ old('end_time', $muhurat->end_time ? substr($muhurat->end_time, 0, 5) : '') }}"></label>
        </div>
        <x-admin.lang-input name="note" label="Note (e.g. nakshatra, tithi)" :value="$muhurat->note ?? []" :required="false" />
        <label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $muhurat->is_active))> Show in app</label>
        <button class="btn">Save</button>
        <a href="{{ route('admin.muhurat.index') }}">Cancel</a>
    </form>
@endsection
