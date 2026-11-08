@extends('admin.layout')
@section('title', 'Edit page')
@section('content')
    <h1>Edit: {{ $page->title['en'] ?? $page->slug }}</h1>
    <form method="post" class="card" action="{{ route('admin.pages.update', $page) }}">
        @csrf @method('PUT')
        <x-admin.lang-input name="title" label="Title" :value="$page->title ?? []" />
        <x-admin.lang-input name="body" label="Text" :value="$page->body ?? []" textarea :required="false" />
        <button class="btn">Save</button>
        <a href="{{ route('admin.pages.index') }}">Cancel</a>
    </form>
@endsection
