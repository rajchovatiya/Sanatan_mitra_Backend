<?php

namespace App\Services\Push;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Firebase Cloud Messaging (HTTP v1) using a service-account key file, without extra packages:
 * a signed JWT is exchanged for an access token, which is cached for 50 minutes.
 */
class FcmPushSender implements PushSender
{
    public function __construct(private string $credentialsPath) {}

    public function sendToTopic(string $topic, array $message): void
    {
        $account = $this->account();

        $notification = array_filter(['title' => $message['title'], 'body' => $message['body'], 'image' => $message['image'] ?? null]);

        Http::withToken($this->accessToken($account))
            ->timeout(15)
            ->post("https://fcm.googleapis.com/v1/projects/{$account['project_id']}/messages:send", ['message' => [
                'topic' => $topic,
                'notification' => $notification,
                'data' => $message['data'] ?? (object) [],
                'android' => ['priority' => 'high'],
            ]])
            ->throw();
    }

    private function account(): array
    {
        $json = is_readable($this->credentialsPath) ? json_decode((string) file_get_contents($this->credentialsPath), true) : null;

        if (! isset($json['client_email'], $json['private_key'], $json['project_id'])) {
            throw new RuntimeException('FIREBASE_CREDENTIALS does not point to a valid service account file.');
        }

        return $json;
    }

    private function accessToken(array $account): string
    {
        return Cache::remember('fcm:token:'.$account['client_email'], now()->addMinutes(50), function () use ($account) {
            $now = time();
            $segments = [
                $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])),
                $this->base64Url(json_encode([
                    'iss' => $account['client_email'],
                    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                    'aud' => 'https://oauth2.googleapis.com/token',
                    'iat' => $now,
                    'exp' => $now + 3600,
                ])),
            ];

            if (! openssl_sign(implode('.', $segments), $signature, $account['private_key'], 'sha256WithRSAEncryption')) {
                throw new RuntimeException('Could not sign the Firebase token.');
            }
            $segments[] = $this->base64Url($signature);

            return Http::asForm()->timeout(15)->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => implode('.', $segments),
            ])->throw()->json('access_token');
        });
    }

    private function base64Url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
