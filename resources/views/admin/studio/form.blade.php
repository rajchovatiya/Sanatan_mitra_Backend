@extends('admin.layout')
@section('title', 'Wallpapers & Videos')
@section('content')
    @php($isVideo = $item->kind === 'video')
    <h1>{{ $item->exists ? 'Edit' : 'Upload' }} {{ $isVideo ? 'video template' : 'wallpaper' }}</h1>
    <form method="post" enctype="multipart/form-data" class="card"
          action="{{ $item->exists ? route('admin.studio.update', $item) : route('admin.studio.store') }}">
        @csrf
        @if ($item->exists) @method('PUT') @endif
        <input type="hidden" name="kind" value="{{ $item->kind }}">

        @include('admin.partials.category-select', ['selected' => $item->category_id])
        <x-admin.lang-input name="title" label="Title" :value="$item->title ?? []" />
        <label>{{ $isVideo ? 'Thumbnail' : 'Wallpaper image' }} {{ $item->exists ? '(leave empty to keep)' : '*' }} – max 3 MB
            @if ($item->image_path)<img class="preview" src="{{ \App\Support\Media::url($item->image_path) }}" alt="">@endif
            <input type="file" name="image" accept="image/*" @required(! $item->exists)>
        </label>
        @if ($isVideo)
            <label>Video (mp4, max 30 MB) {{ $item->exists ? '(leave empty to keep)' : '*' }}
                <input type="file" name="video" accept="video/mp4" @required(! $item->exists)>
            </label>
        @endif
        <x-admin.common-fields :model="$item" />
        <button class="btn">Save</button>
        <a href="{{ route('admin.studio.index', ['kind' => $item->kind]) }}">Cancel</a>
    </form>
@endsection
