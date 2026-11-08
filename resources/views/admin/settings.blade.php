@extends('admin.layout')
@section('title', 'App settings')
@section('content')
    <h1>App settings</h1>
    <form method="post" class="card" action="{{ route('admin.settings.update') }}">
        @csrf @method('PUT')
        <fieldset class="langs">
            <legend>App versions</legend>
            <p><small>Phones older than the minimum version must update before using the app.</small></p>
            <div class="row">
                <label>Android minimum <input type="text" name="android_min_version" value="{{ old('android_min_version', $config['android_min_version']) }}"></label>
                <label>iOS minimum <input type="text" name="ios_min_version" value="{{ old('ios_min_version', $config['ios_min_version']) }}"></label>
                <label>Latest version <input type="text" name="latest_version" value="{{ old('latest_version', $config['latest_version']) }}"></label>
            </div>
        </fieldset>

        <fieldset class="langs">
            <legend>Maintenance</legend>
            <label class="check"><input type="checkbox" name="maintenance" value="1" @checked(old('maintenance', $config['maintenance']))> App is under maintenance (users see the message below)</label>
            <label>Message <input type="text" name="maintenance_message" value="{{ old('maintenance_message', $config['maintenance_message']) }}"></label>
        </fieldset>

        <fieldset class="langs">
            <legend>Support contact</legend>
            <div class="row">
                <label>Email <input type="email" name="support_email" value="{{ old('support_email', $config['support_email']) }}"></label>
                <label>WhatsApp number <input type="text" name="support_whatsapp" value="{{ old('support_whatsapp', $config['support_whatsapp']) }}" placeholder="+919876543210"></label>
            </div>
        </fieldset>

        <fieldset class="langs">
            <legend>Features shown in the app</legend>
            <div class="row">
                @foreach (config('sanatan.app.features') as $key => $default)
                    <label class="check">
                        <input type="checkbox" name="features[{{ $key }}]" value="1" @checked(old("features.{$key}", $config['features'][$key] ?? $default))>
                        {{ ucfirst($key) }}
                    </label>
                @endforeach
            </div>
        </fieldset>

        <button class="btn">Save settings</button>
    </form>
@endsection
