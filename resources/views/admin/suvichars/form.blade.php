@extends('admin.layout')
@section('title', 'Suvichar')
@section('content')
    <h1>{{ $suvichar->exists ? 'Edit' : 'Add' }} suvichar</h1>
    <form method="post" class="card"
          action="{{ $suvichar->exists ? route('admin.suvichars.update', $suvichar) : route('admin.suvichars.store') }}">
        @csrf
        @if ($suvichar->exists) @method('PUT') @endif
        <label>Date (optional: only for a special day)
            <input type="date" name="date" value="{{ old('date', $suvichar->date?->format('Y-m-d')) }}">
        </label>
        <x-admin.lang-input name="text" label="Suvichar" :value="$suvichar->text ?? []" textarea />
        <x-admin.lang-input name="author" label="Author / source" :value="$suvichar->author ?? []" :required="false" />
        <label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $suvichar->is_active))> Show in app</label>
        <button class="btn">Save</button>
        <a href="{{ route('admin.suvichars.index') }}">Cancel</a>
    </form>
@endsection
