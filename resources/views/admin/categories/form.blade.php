@extends('admin.layout')
@section('title', $category->exists ? 'Edit category' : 'Add category')
@section('content')
    <h1>{{ $category->exists ? 'Edit category' : 'Add category' }}</h1>
    <form method="post" enctype="multipart/form-data" class="card"
          action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
        @csrf
        @if ($category->exists) @method('PUT') @endif

        <label>Module
            @if ($category->exists)
                <input type="hidden" name="type" value="{{ $category->type }}">
                <input type="text" value="{{ \App\Models\Category::TYPES[$category->type] }}" disabled>
            @else
                <select name="type">
                    @foreach (\App\Models\Category::TYPES as $key => $label)
                        <option value="{{ $key }}" @selected(old('type', $category->type) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            @endif
        </label>

        <x-admin.lang-input name="name" label="Name" :value="$category->name ?? []" />
        <x-admin.lang-input name="description" label="Description (optional, e.g. about this muhurat)" :value="$category->description ?? []" textarea :required="false" />

        <label>Icon (optional, jpg/png/webp, max 3 MB)
            @if ($category->icon_path)<img class="thumb" src="{{ \App\Support\Media::url($category->icon_path) }}" alt="">@endif
            <input type="file" name="icon" accept="image/*">
        </label>

        <x-admin.common-fields :model="$category" />
        <button class="btn">Save</button>
        <a href="{{ route('admin.categories.index') }}">Cancel</a>
    </form>
@endsection
