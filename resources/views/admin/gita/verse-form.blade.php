@extends('admin.layout')
@section('title', 'Gita verse')
@section('content')
    <h1>{{ $verse->exists ? 'Edit' : 'Add' }} Gita verse</h1>
    <form method="post" class="card"
          action="{{ $verse->exists ? route('admin.gita-verses.update', $verse) : route('admin.gita-verses.store') }}">
        @csrf
        @if ($verse->exists) @method('PUT') @endif

        <div class="row">
            <label>Chapter
                <select name="chapter">
                    @foreach ($chapters as $c)
                        <option value="{{ $c->number }}" @selected((int) old('chapter', $verse->chapter) === $c->number)>{{ $c->number }}. {{ $c->name['en'] ?? '' }} ({{ $c->verse_count }} verses)</option>
                    @endforeach
                </select>
            </label>
            <label>Verse number <input type="number" name="verse" min="1" value="{{ old('verse', $verse->verse) }}" required></label>
        </div>
        <label>Sanskrit * <textarea name="sanskrit" rows="3" required>{{ old('sanskrit', $verse->sanskrit) }}</textarea></label>
        <label>Transliteration (optional) <textarea name="transliteration" rows="2">{{ old('transliteration', $verse->transliteration) }}</textarea></label>
        <x-admin.lang-input name="meaning" label="Meaning" :value="$verse->meaning ?? []" textarea />
        <label class="check"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $verse->is_featured))> Use as "verse of the day"</label>
        <button class="btn">Save</button>
        <a href="{{ route('admin.gita-verses.index', ['chapter' => $verse->chapter]) }}">Cancel</a>
    </form>
@endsection
