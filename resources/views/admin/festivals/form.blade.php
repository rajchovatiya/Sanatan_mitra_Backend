@extends('admin.layout')
@section('title', $festival->exists ? 'Edit festival' : 'Add festival')
@section('content')
    <h1>{{ $festival->exists ? 'Edit festival' : 'Add festival' }}</h1>
    <form method="post" enctype="multipart/form-data" class="card"
          action="{{ $festival->exists ? route('admin.festivals.update', $festival) : route('admin.festivals.store') }}">
        @csrf
        @if ($festival->exists) @method('PUT') @endif

        <div class="row">
            <label>Date *
                <input type="date" name="date" value="{{ old('date', $festival->date?->format('Y-m-d')) }}" required>
            </label>
            <label class="check">
                <input type="checkbox" name="is_vrat" value="1" @checked(old('is_vrat', $festival->is_vrat))> This is a Vrat (fast)
            </label>
        </div>

        <x-admin.lang-input name="name" label="Name" :value="$festival->name ?? []" />
        <x-admin.lang-input name="tithi" label="Tithi (e.g. Kartika Amavasya)" :value="$festival->tithi ?? []" :required="false" />
        <x-admin.lang-input name="about" label="About" :value="$festival->about ?? []" textarea :required="false" />
        <x-admin.lang-input name="rituals" label="Puja vidhi / rituals (one step per line)" :value="$festival->rituals ?? []" textarea :required="false" />

        <label>Image (optional, max 3 MB)
            @if ($festival->image_path)<img class="preview" src="{{ \App\Support\Media::url($festival->image_path) }}" alt="">@endif
            <input type="file" name="image" accept="image/*">
        </label>

        <x-admin.common-fields :model="$festival" />
        <button class="btn">Save</button>
        <a href="{{ route('admin.festivals.index') }}">Cancel</a>
    </form>
@endsection
