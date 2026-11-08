@extends('admin.layout')
@section('title', 'Gita chapter')
@section('content')
    <h1>Chapter {{ $chapter->number }}</h1>
    <form method="post" class="card" action="{{ route('admin.gita.chapters.update', $chapter) }}">
        @csrf @method('PUT')
        <x-admin.lang-input name="name" label="Name" :value="$chapter->name ?? []" />
        <x-admin.lang-input name="summary" label="Summary" :value="$chapter->summary ?? []" textarea :required="false" />
        <button class="btn">Save</button>
        <a href="{{ route('admin.gita.chapters') }}">Cancel</a>
    </form>
@endsection
