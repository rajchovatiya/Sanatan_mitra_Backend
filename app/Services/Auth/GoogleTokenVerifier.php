<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Http;

/**
 * Checks a Google Sign-In ID token with Google's tokeninfo endpoint (free, no
 * extra package). Returns the verified claims, or null when the token is
 * invalid, expired, not for this app, or the email is not verified.
 */
class GoogleTokenVerifier
{
    /** @return array{sub: string, email: string, name: ?string}|null */
    public function verify(string $idToken): ?array
    {
        $allowed = config('sanatan.google.client_ids');
        if ($allowed === []) {
            return null;
        }

        $res = Http::timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', ['id_token' => $idToken]);
        if (! $res->ok()) {
            return null;
        }

        $c = $res->json();
        $issuerOk = in_array($c['iss'] ?? '', ['accounts.google.com', 'https://accounts.google.com'], true);
        $audienceOk = in_array($c['aud'] ?? '', $allowed, true);
        $verified = ($c['email_verified'] ?? false) === true || ($c['email_verified'] ?? '') === 'true';
        $fresh = (int) ($c['exp'] ?? 0) > time();

        if (! $issuerOk || ! $audienceOk || ! $verified || ! $fresh || empty($c['sub']) || empty($c['email'])) {
            return null;
        }

        return ['sub' => (string) $c['sub'], 'email' => strtolower($c['email']), 'name' => $c['name'] ?? null];
    }
}
