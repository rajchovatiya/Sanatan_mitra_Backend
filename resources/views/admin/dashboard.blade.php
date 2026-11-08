@extends('admin.layout')
@section('title', 'Dashboard')
@section('content')
    <h1>Dashboard</h1>
    <div class="stats">
        @foreach ($counts as $label => $count)
            <div class="card stat"><strong>{{ number_format($count) }}</strong><span>{{ $label }}</span></div>
        @endforeach
    </div>
@endsection
