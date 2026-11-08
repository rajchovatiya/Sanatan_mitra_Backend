@extends('admin.layout')
@section('title', 'Live & Courses')
@section('content')
    <h1>{{ $video->exists ? 'Edit' : 'Add' }} {{ $video->kind === 'lesson' ? 'course lesson' : 'live video' }}</h1>
    <form method="post" enctype="multipart/form-data" class="card"
          action="{{ $video->exists ? route('admin.learning.update', $video) : route('admin.learning.store') }}">
        @csrf
        @if ($video->exists) @method('PUT') @endif
        <input type="hidden" name="kind" value="{{ $video->kind }}">

        @if ($video->kind === 'lesson')
            <label>Course *
                <select name="category_id" required>
                    <option value="">Choose…</option>
                    @foreach ($courses as $c)
                        <option value="{{ $c->id }}" @selected((int) old('category_id', $video->category_id) === $c->id)>{{ $c->name['en'] ?? '' }}</option>
                    @endforeach
                </select>
                @if ($courses->isEmpty())<small>Add a course first in Categories (module "Courses").</small>@endif
            </label>
        @endif
        <x-admin.lang-input name="title" label="Title" :value="$video->title ?? []" />
        <x-admin.lang-input name="description" label="Description" :value="$video->description ?? []" textarea :required="false" />
        <label>YouTube / video link * (https://…) <input type="url" name="video_url" value="{{ old('video_url', $video->video_url) }}" required></label>
        <label>Thumbnail (optional)
            @if ($video->thumbnail_path)<img class="thumb" src="{{ \App\Support\Media::url($video->thumbnail_path) }}" alt="">@endif
            <input type="file" name="thumbnail" accept="image/*">
        </label>
        <x-admin.common-fields :model="$video" />
        <button class="btn">Save</button>
        <a href="{{ route('admin.learning.index', ['kind' => $video->kind]) }}">Cancel</a>
    </form>
@endsection
