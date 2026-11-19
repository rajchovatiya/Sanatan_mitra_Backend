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
            <label>Show in app's "Today All" on (optional)
                <input type="date" name="today_on" value="{{ old('today_on', $poster->today_on?->format('Y-m-d')) }}">
            </label>
        </div>

        <label class="check">
            <input type="checkbox" name="footer_below" value="1" @checked(old('footer_below', $poster->footer_below))>
            Poster is full of text: put the user's name and contacts in a strip <b>below</b> the picture (not over it)
        </label>


        <fieldset class="langs">
            <legend>Show first on these days (optional)</legend>
            <p><small>On a ticked day this poster comes at the top of the Album (e.g. Shiv ji on Monday). Nothing ticked = no particular day.</small></p>
            <div class="row">
                @foreach (\App\Models\Poster::WEEKDAYS as $num => $day)
                    <label class="check">
                        <input type="checkbox" name="weekdays[]" value="{{ $num }}" @checked(in_array($num, (array) old('weekdays', $poster->weekdays ?? [])))> {{ $day }}
                    </label>
                @endforeach
            </div>
        </fieldset>

        <fieldset class="langs">
            <legend>Date on the poster</legend>
            <label class="check">
                <input type="checkbox" name="show_date" value="1" @checked(old('show_date', $poster->show_date ?? false))>
                Show a date box on this poster
            </label>
            <div class="row">
                <label>Where
                    <select name="date_position">
                        @foreach (\App\Models\Poster::DATE_POSITIONS as $key => $label)
                            <option value="{{ $key }}" @selected(old('date_position', $poster->date_position ?? 'top_right') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Fixed date (optional; empty = the day the user shares it)
                    <input type="date" name="date_on" value="{{ old('date_on', $poster->date_on?->format('Y-m-d')) }}">
                </label>
            </div>
        </fieldset>

        <x-admin.common-fields :model="$poster" />
        <button class="btn">Save</button>
        <a href="{{ route('admin.posters.index') }}">Cancel</a>
    </form>
@endsection
