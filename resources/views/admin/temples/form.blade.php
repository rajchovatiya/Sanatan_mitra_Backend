@extends('admin.layout')
@section('title', $temple->exists ? 'Edit temple' : 'Add temple')
@section('content')
    <h1>{{ $temple->exists ? 'Edit temple' : 'Add temple' }}</h1>
    <form method="post" enctype="multipart/form-data" class="card"
          action="{{ $temple->exists ? route('admin.temples.update', $temple) : route('admin.temples.store') }}">
        @csrf
        @if ($temple->exists) @method('PUT') @endif

        <div class="row">
            <label>Type
                <select name="type">
                    @foreach (\App\Models\Temple::TYPES as $key => $label)
                        <option value="{{ $key }}" @selected(old('type', $temple->type) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>State (e.g. Gujarat) <input type="text" name="state" value="{{ old('state', $temple->state) }}"></label>
        </div>

        <x-admin.lang-input name="name" label="Name" :value="$temple->name ?? []" />
        <x-admin.lang-input name="location" label="Location / city" :value="$temple->location ?? []" :required="false" />
        <x-admin.lang-input name="deity" label="Deity" :value="$temple->deity ?? []" :required="false" />
        <x-admin.lang-input name="about" label="About" :value="$temple->about ?? []" textarea :required="false" />
        <x-admin.lang-input name="timings" label="Darshan timings" :value="$temple->timings ?? []" :required="false" />
        <x-admin.lang-input name="best_time" label="Best time to visit" :value="$temple->best_time ?? []" :required="false" />

        <div class="row">
            <label>Latitude (for map) <input type="number" step="any" name="lat" value="{{ old('lat', $temple->lat) }}"></label>
            <label>Longitude <input type="number" step="any" name="lng" value="{{ old('lng', $temple->lng) }}"></label>
            <label>Photo (max 3 MB)
                @if ($temple->image_path)<img class="thumb" src="{{ \App\Support\Media::url($temple->image_path) }}" alt="">@endif
                <input type="file" name="image" accept="image/*">
            </label>
        </div>

        <x-admin.common-fields :model="$temple" />
        <button class="btn">Save</button>
        <a href="{{ route('admin.temples.index') }}">Cancel</a>
    </form>
@endsection
