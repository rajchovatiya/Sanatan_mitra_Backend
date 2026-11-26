@extends('public.layout')
@section('title', 'Delete your account')
@section('content')
    <p>You can permanently delete your <b>Sanatan Shaily</b> account and its data in two ways.</p>

    <div class="card">
        <h2 style="margin-top:0">1. In the app (immediate)</h2>
        <p>Open the app → <b>Profile</b> → <b>Delete account</b> → confirm. Your account is deleted right away.</p>
    </div>

    <div class="card {{ session('sent') ? 'ok' : '' }}">
        <h2 style="margin-top:0">2. Request it here</h2>
        @if (session('sent'))
            <p><b>Request received.</b> We will delete the account for this email address within 7 days.</p>
        @else
            <p>No longer have the app? Enter the Google email address you sign in with. We will delete that account within 7 days.</p>
            <form method="post" action="{{ route('public.delete-account.request') }}">
                @csrf
                <label for="email">Google email address</label>
                <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required>
                @error('email') <div class="error">Please enter a valid email address.</div> @enderror
                <label for="note">Note (optional)</label>
                <textarea id="note" name="note" rows="3" maxlength="500">{{ old('note') }}</textarea>
                <button type="submit">Request deletion</button>
            </form>
        @endif
    </div>

    <h2>What is deleted</h2>
    <ul>
        <li>Your profile: name, email, Google account link, mobile number, birth details, city, business details</li>
        <li>Saved Kundlis and favourites</li>
        <li>Your logged-in sessions</li>
    </ul>
    <p class="muted">Feedback messages you sent may be kept without any link to your account. Photos and posters are
        stored only on your phone; uninstalling the app removes them.</p>
@endsection
