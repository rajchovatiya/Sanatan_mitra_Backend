@extends('admin.layout')
@section('title', $mantra->exists ? 'Edit mantra' : 'Add mantra')
@section('content')
    <h1>{{ $mantra->exists ? 'Edit mantra' : 'Add mantra' }}</h1>
    <form method="post" enctype="multipart/form-data" class="card"
          action="{{ $mantra->exists ? route('admin.mantras.update', $mantra) : route('admin.mantras.store') }}">
        @csrf
        @if ($mantra->exists) @method('PUT') @endif

        @include('admin.partials.category-select', ['selected' => $mantra->category_id])
        <x-admin.lang-input name="title" label="Title" :value="$mantra->title ?? []" />
        <x-admin.lang-input name="deity" label="Deity (e.g. Maa Gayatri)" :value="$mantra->deity ?? []" />
        <x-admin.lang-input name="lyrics" label="Lyrics (Sanskrit is not translated)" :value="$mantra->lyrics ?? []" textarea :translate="false" />
        <x-admin.lang-input name="meaning" label="Meaning" :value="$mantra->meaning ?? []" textarea :required="false" />

        <div class="row">
            <label>Deity image (optional, max 3 MB)
                @if ($mantra->image_path)<img class="thumb round" src="{{ \App\Support\Media::url($mantra->image_path) }}" alt="">@endif
                <input type="file" name="image" accept="image/*">
            </label>
            <label>Audio (mp3/m4a/aac, max 20 MB)
                @if ($mantra->audio_path)<audio controls preload="none" src="{{ \App\Support\Media::url($mantra->audio_path) }}"></audio>@endif
                <input type="file" name="audio" accept="audio/*">
            </label>
            <label>Duration in seconds (optional)
                <input type="number" name="duration_seconds" min="1" value="{{ old('duration_seconds', $mantra->duration_seconds) }}">
            </label>
        </div>

        <x-admin.common-fields :model="$mantra" />
        <button class="btn">Save</button>
        <a href="{{ route('admin.mantras.index') }}">Cancel</a>
    </form>
@endsection
