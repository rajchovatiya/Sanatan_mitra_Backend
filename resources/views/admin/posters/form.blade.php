@extends('admin.layout')
@section('title', $poster->exists ? 'Edit poster' : 'Upload poster')
@section('content')
    <h1>{{ $poster->exists ? 'Edit poster' : 'Upload poster' }}</h1>
    <form method="post" enctype="multipart/form-data" class="card"
          action="{{ $poster->exists ? route('admin.posters.update', $poster) : route('admin.posters.store') }}">
        @csrf
        @if ($poster->exists) @method('PUT') @endif

        @include('admin.partials.category-select', ['selected' => $poster->category_id])
        <x-admin.lang-input name="title" label="Title" :value="$poster->title ?? []" />

        <label>Poster image {{ $poster->exists ? '(leave empty to keep)' : '*' }} – jpg/png/webp, max 3 MB
            @if ($poster->image_path)<img class="preview" src="{{ \App\Support\Media::url($poster->image_path) }}" alt="">@endif
            <input type="file" name="image" accept="image/*" @required(! $poster->exists)>
        </label>

        <div class="row">
            <label>Frame (where the user's name and photo go)
                <select name="frame">
                    @foreach (\App\Models\Poster::FRAMES as $frame)
                        <option value="{{ $frame }}" @selected(old('frame', $poster->frame) === $frame)>{{ $frame }}</option>
                    @endforeach
                </select>
            </label>
            <label>Show from date (optional, e.g. festival day)
                <input type="date" name="show_from" value="{{ old('show_from', $poster->show_from?->format('Y-m-d')) }}">
            </label>
        </div>

        <x-admin.common-fields :model="$poster" />
        <button class="btn">Save</button>
        <a href="{{ route('admin.posters.index') }}">Cancel</a>
    </form>
@endsection
