@extends('admin.layout')
@section('title', 'Guide')
@section('content')
    <h1>{{ $guide->exists ? 'Edit' : 'Add' }} {{ \App\Models\Guide::MODULES[$guide->module] ?? 'guide' }}</h1>
    <form method="post" enctype="multipart/form-data" class="card"
          action="{{ $guide->exists ? route('admin.guides.update', $guide) : route('admin.guides.store') }}">
        @csrf
        @if ($guide->exists) @method('PUT') @endif

        <label>Module
            <select name="module">
                @foreach (\App\Models\Guide::MODULES as $key => $label)
                    <option value="{{ $key }}" @selected(old('module', $guide->module) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <x-admin.lang-input name="title" label="Title (e.g. Ruby, 5 Mukhi, The Fool, Number 1, Heart Line)" :value="$guide->title ?? []" />
        <x-admin.lang-input name="subtitle" label="Subtitle (e.g. Hindi name, keyword)" :value="$guide->subtitle ?? []" :required="false" />
        <x-admin.lang-input name="body" label="Description" :value="$guide->body ?? []" textarea :required="false" />
        <x-admin.lang-input name="facts" label="Facts – one per line as Label: value (e.g. Planet: Surya)" :value="$guide->facts ?? []" textarea :required="false" />
        <x-admin.lang-input name="points" label="Points – one per line (benefits, readings…)" :value="$guide->points ?? []" textarea :required="false" />
        <label>Image (optional)
            @if ($guide->image_path)<img class="thumb" src="{{ \App\Support\Media::url($guide->image_path) }}" alt="">@endif
            <input type="file" name="image" accept="image/*">
        </label>
        <x-admin.common-fields :model="$guide" />
        <button class="btn">Save</button>
        <a href="{{ route('admin.guides.index', ['module' => $guide->module]) }}">Cancel</a>
    </form>
@endsection
