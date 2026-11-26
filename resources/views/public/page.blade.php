@extends('public.layout')
@section('title', $title)
@section('langs')
    <div class="langs">
        <a href="?lang=en">English</a><a href="?lang=hi">हिन्दी</a><a href="?lang=gu">ગુજરાતી</a>
    </div>
@endsection
@section('content')
    @php($email = config('app.support_email'))

    @if ($body !== '')
        {{-- Text written in Admin → Pages. --}}
        <div class="body">{{ $body }}</div>

    @elseif ($slug === 'privacy')
        <p class="muted">Last updated: 26 November 2026</p>
        <p>This policy explains what the Sanatan Shaily app (“the app”, “we”) collects, why, and the choices you have.
            By using the app you agree to this policy.</p>

        <h2>Using the app without an account</h2>
        <p>You can tap <b>Continue as Guest</b>. As a guest, your profile and posters stay only on your phone and nothing
            about you is stored on our servers. Kundli and Kundli Matching need a Google account.</p>

        <h2>What we collect</h2>
        <ul>
            <li><b>Google account</b>: when you sign in with Google we receive your name, email address and Google account
                ID, to create and identify your account. We never see your Google password.</li>
            <li><b>Profile details you choose to enter</b>: name, mobile number (optional, printed on your posters), gender,
                date, time and place of birth, city and app language.</li>
            <li><b>Business details you choose to enter</b>: business name, type, address and other details, shown on the
                posters you make.</li>
            <li><b>Saved items</b>: Kundlis you save and items you mark as favourite.</li>
            <li><b>Location (only if you allow it)</b>: your phone’s location is sent to our server only to find the
                nearest town, so Panchang timings (sunrise, Rahu Kaal, etc.) are correct for where you are. It is used for
                that request and not stored. You can pick a city by hand instead.</li>
            <li><b>Feedback</b> you send us, with the contact details you add to it.</li>
        </ul>
        <p>We do not use analytics or advertising SDKs, do not track you across apps and do not collect device advertising IDs.</p>

        <h2>Photos</h2>
        <p>Your profile photo, business logo and the posters you create stay on your phone; they are not uploaded to our
            servers. The app reads a photo only when you pick it with the phone’s photo picker. Removing the background from
            your photo happens on your phone. Posters leave your phone only when you tap Share, Status or Download.</p>

        <h2>How we use it</h2>
        <ul>
            <li>To run the app’s features: sign-in, Kundli, Kundli Matching, Panchang, Rashifal, posters and saved items.</li>
            <li>To keep the service secure and fix problems.</li>
        </ul>
        <p>We do <b>not</b> sell your personal data and do not use it for advertising.</p>

        <h2>Who we share it with</h2>
        <ul>
            <li><b>Astrology calculation provider</b> (Prokerala): birth date, time and place are sent only to calculate your
                Kundli and Kundli Matching.</li>
            <li><b>Google</b>: to sign you in with your Google account.</li>
            <li><b>Hosting provider</b> that runs our servers (data is stored on their servers for us).</li>
            <li>Authorities, only when required by law.</li>
        </ul>
        <p>All data is sent over encrypted connections (HTTPS).</p>

        <h2>Keeping and deleting your data</h2>
        <p>We keep your data while you have an account. You can delete your account at any time in the app:
            <b>Profile → Delete account</b>, or by request on our
            <a href="{{ route('public.delete-account') }}">account deletion page</a>. This permanently deletes your
            profile, Google account link, saved Kundlis and favourites from our servers. Feedback messages may be kept
            without your account link. Signing out or uninstalling the app does not delete the account.</p>

        <h2>Children</h2>
        <p>The app is not meant for children under 13, and we do not knowingly collect their data.</p>

        <h2>Changes</h2>
        <p>We may update this policy. The new version will be posted on this page with a new date.</p>

        <h2>Contact</h2>
        <p>Questions or requests about your data:
            @if ($email) <a href="mailto:{{ $email }}">{{ $email }}</a>. @else use the contact email on the app’s Google Play listing. @endif
            You can also use our <a href="{{ route('public.delete-account') }}">account deletion page</a>.</p>
    @else
        <p class="muted">Last updated: 18 November 2026</p>
        <p>By using the Sanatan Shaily app you agree to these terms.</p>

        <h2>Use of the app</h2>
        <ul>
            <li>Use the app only for lawful, personal purposes.</li>
            <li>You are responsible for the text, photos and business details you add to posters and for how you share them.</li>
            <li>Do not misuse the app, try to break it, or copy its content in bulk.</li>
        </ul>

        <h2>Religious and astrology content</h2>
        <p>Panchang, Kundli, Rashifal, Muhurat, gemstone and similar content is for devotional and general guidance only.
            It is not professional, medical, legal or financial advice. Please consult a qualified person before important
            decisions.</p>

        <h2>Content and copyright</h2>
        <p>Content in the app belongs to us or to its owners. If you believe something in the app uses your work without
            permission, contact us and we will review and remove it promptly.</p>

        <h2>Account</h2>
        <p>You can delete your account at any time (<b>Profile → Delete account</b>). We may block accounts that misuse
            the app.</p>

        <h2>Changes and availability</h2>
        <p>We may change or stop features and update these terms. Continued use means you accept the updated terms.</p>

        <h2>Contact</h2>
        <p>@if ($email) <a href="mailto:{{ $email }}">{{ $email }}</a> @else Use the contact email on the app’s Google Play listing. @endif</p>
    @endif
@endsection
