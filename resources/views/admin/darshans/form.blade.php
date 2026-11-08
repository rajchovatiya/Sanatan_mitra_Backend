@extends('admin.layout')
@section('title', 'Daily Darshan')
@section('content')
    <h1>{{ $darshan->exists ? 'Edit darshan' : 'Add darshan' }}</h1>
    <form method="post" enctype="multipart/form-data" class="card"
          action="{{ $darshan->exists ? route('admin.darshans.update', $darshan) : route('admin.darshans.store') }}">
        @csrf
        @if ($darshan->exists) @method('PUT') @endif

        <div class="row">
            <label>Date * <input type="date" name="date" value="{{ old('date', $darshan->date?->format('Y-m-d')) }}" required></label>
            <label>Temple (optional)
                <select name="temple_id">
                    <option value="">—</option>
                    @foreach ($temples as $t)
                        <option value="{{ $t->id }}" @selected((int) old('temple_id', $darshan->temple_id) === $t->id)>{{ $t->name['en'] ?? '' }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        <x-admin.lang-input name="title" label="Title (e.g. Somnath Shringar Darshan)" :value="$darshan->title ?? []" />
        <label>Photo {{ $darshan->exists ? '(leave empty to keep)' : '*' }}
            @if ($darshan->image_path)<img class="preview" src="{{ \App\Support\Media::url($darshan->image_path) }}" alt="">@endif
            <input type="file" name="image" accept="image/*" @required(! $darshan->exists)>
        </label>
        <label>Live / video link (optional, https://…) <input type="url" name="video_url" value="{{ old('video_url', $darshan->video_url) }}"></label>
        <label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $darshan->is_active))> Show in app</label>
        <button class="btn">Save</button>
        <a href="{{ route('admin.darshans.index') }}">Cancel</a>
    </form>
@endsection
