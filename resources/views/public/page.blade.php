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
        <p class="muted">Last updated: 18 November 2026</p>
        <p>This policy explains what the Sanatan Shaily app (“the app”, “we”) collects, why, and the choices you have.
            By using the app you agree to this policy.</p>

        <h2>What we collect</h2>
        <ul>
            <li><b>Mobile number</b>: to log you in with a one-time code (OTP).</li>
            <li><b>Profile details you choose to enter</b>: name, gender, date, time and place of birth, city and app language.
                Birth details are used to calculate your Kundli, Panchang and Rashifal.</li>
            <li><b>Business details you choose to enter</b>: business name, type, address and other details, shown on the
                posters you make.</li>
            <li><b>Saved items</b>: Kundlis you save and items you mark as favourite.</li>
            <li><b>Location (only if you allow it)</b>: your device location is used to find your nearest city so
                Panchang timings (sunrise, Rahu Kaal, etc.) are correct. We do not store your exact location on our servers.</li>
            <li><b>Device information</b>: app version, phone type and a notification token, to send notifications you
                have switched on.</li>
            <li><b>Feedback</b> you send us, with the contact details you add to it.</li>
        </ul>

        <h2>Photos</h2>
        <p>Your profile photo, business logo and the posters you create are stored on your phone. The app reads your photos
            only when you pick one. Posters are shared only when you tap Share.</p>

        <h2>How we use it</h2>
        <ul>
            <li>To run the app’s features: login, Kundli, Panchang, Rashifal, posters and saved items.</li>
            <li>To send notifications you have turned on (festivals, daily Panchang). You can turn them off in Settings.</li>
            <li>To keep the service secure and fix problems.</li>
        </ul>
        <p>We do <b>not</b> sell your personal data and do not use it for advertising.</p>

        <h2>Who we share it with</h2>
        <ul>
            <li><b>Astrology calculation provider</b>: birth date, time and place are sent to calculate charts.</li>
            <li><b>Hosting and notification services</b> that run our servers and deliver notifications.</li>
            <li><b>SMS provider</b> to deliver your login code.</li>
            <li>Authorities, only when required by law.</li>
        </ul>
        <p>Data is sent over encrypted connections (HTTPS).</p>

        <h2>Keeping and deleting your data</h2>
        <p>We keep your data while you have an account. You can delete your account at any time in the app:
            <b>Profile → Delete account</b>, or by request on our
            <a href="{{ route('public.delete-account') }}">account deletion page</a>. This permanently deletes your
            profile, saved Kundlis, favourites and devices from our servers. Feedback messages may be kept without your
            account link.</p>

        <h2>Children</h2>
        <p>The app is not meant for children under 13, and we do not knowingly collect their data.</p>

        <h2>Changes</h2>
        <p>We may update this policy. The new version will be posted on this page with a new date.</p>

        <h2>Contact</h2>
        <p>Questions or requests about your data:
            @if ($email) <a href="mailto:{{ $email }}">{{ $email }}</a>. @endif
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
