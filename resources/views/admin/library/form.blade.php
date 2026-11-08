@extends('admin.layout')
@section('title', 'Katha & Aarti')
@section('content')
    <h1>{{ $item->exists ? 'Edit' : 'Add' }} Katha / Aarti / Chalisa</h1>
    <form method="post" enctype="multipart/form-data" class="card"
          action="{{ $item->exists ? route('admin.library.update', $item) : route('admin.library.store') }}">
        @csrf
        @if ($item->exists) @method('PUT') @endif

        <label>Type
            <select name="type">
                @foreach (\App\Models\LibraryItem::TYPES as $key => $label)
                    <option value="{{ $key }}" @selected(old('type', $item->type) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <x-admin.lang-input name="title" label="Title" :value="$item->title ?? []" />
        <x-admin.lang-input name="subtitle" label="Subtitle (e.g. deity or occasion)" :value="$item->subtitle ?? []" :required="false" />
        <x-admin.lang-input name="body" label="Full text" :value="$item->body ?? []" textarea />

        <div class="row">
            <label>Image (optional)
                @if ($item->image_path)<img class="thumb" src="{{ \App\Support\Media::url($item->image_path) }}" alt="">@endif
                <input type="file" name="image" accept="image/*">
            </label>
            <label>Audio (mp3/m4a/aac, max 20 MB)
                @if ($item->audio_path)<audio controls preload="none" src="{{ \App\Support\Media::url($item->audio_path) }}"></audio>@endif
                <input type="file" name="audio" accept="audio/*">
            </label>
        </div>

        <x-admin.common-fields :model="$item" />
        <button class="btn">Save</button>
        <a href="{{ route('admin.library.index') }}">Cancel</a>
    </form>
@endsection
