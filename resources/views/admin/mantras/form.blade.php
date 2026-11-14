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
        <x-admin.lang-input name="lyrics" label="Lyrics (optional – without lyrics the app shows the deity picture; Sanskrit is not translated)" :value="$mantra->lyrics ?? []" textarea :translate="false" :required="false" />
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

        @if ($mantra->audio_path)
            {{-- Tap-to-sync: play the audio and press "Mark line" as each line starts. --}}
            <fieldset class="lyrics-sync" data-audio="{{ \App\Support\Media::url($mantra->audio_path) }}">
                <legend>Sync lyrics with the audio</legend>
                <p class="hint">
                    Choose a language, press <b>Start</b>, then press <b>Mark line</b> (or the Space key) each time a new line
                    begins. Times like <code>[01:05.20]</code> are added in front of the lines. Press <b>Save</b> when done.
                    In the app the line being sung is highlighted, and tapping a line plays from there.
                </p>
                <div class="row">
                    <label>Language
                        <select data-sync-lang>
                            <option value="en">English</option>
                            <option value="hi">हिंदी</option>
                            <option value="gu">ગુજરાતી</option>
                        </select>
                    </label>
                    <label>Audio speed (times stay correct at any speed)
                        <select data-sync-speed>
                            <option value="1">1× normal</option>
                            <option value="1.5">1.5×</option>
                            <option value="2">2×</option>
                            <option value="3">3×</option>
                            <option value="4">4× fastest</option>
                        </select>
                    </label>
                </div>
                <audio data-sync-audio controls preload="metadata" src="{{ \App\Support\Media::url($mantra->audio_path) }}"></audio>
                <div class="row">
                    <button type="button" class="btn" data-sync-start>Start</button>
                    <button type="button" class="btn" data-sync-mark disabled>Mark line ▸</button>
                    <button type="button" class="btn ghost" data-sync-undo disabled>Undo</button>
                    <button type="button" class="btn ghost" data-sync-clear>Remove times</button>
                </div>
                <p data-sync-status class="hint"></p>
            </fieldset>
            <script src="{{ asset('assets/js/admin-lyrics-sync.js') }}" defer></script>
        @else
            <p class="hint">Upload the audio and save once; then a "Sync lyrics with the audio" tool appears here.</p>
        @endif

        <x-admin.common-fields :model="$mantra" />
        <button class="btn">Save</button>
        <a href="{{ route('admin.mantras.index') }}">Cancel</a>
    </form>
@endsection
