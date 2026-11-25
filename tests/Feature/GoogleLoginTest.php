<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['sanatan.google.client_ids' => ['web-client.apps.googleusercontent.com']]);
    }

    private function fakeGoogle(array $claims = [], int $status = 200): void
    {
        Http::fake(['oauth2.googleapis.com/*' => Http::response(array_merge([
            'iss' => 'https://accounts.google.com',
            'aud' => 'web-client.apps.googleusercontent.com',
            'sub' => '1234567890',
            'email' => 'Bhakt@Gmail.com',
            'email_verified' => 'true',
            'name' => 'Raj Bhakt',
            'exp' => (string) (time() + 600),
        ], $claims), $status)]);
    }

    public function test_first_google_login_creates_user_and_token_works(): void
    {
        $this->fakeGoogle();

        $token = $this->postJson('/api/v1/auth/google', ['id_token' => 'abc'])
            ->assertOk()
            ->assertJsonPath('is_new', true)
            ->assertJsonPath('user.email', 'bhakt@gmail.com')
            ->assertJsonPath('user.name', 'Raj Bhakt')
            ->assertJsonPath('user.mobile', null)
            ->json('token');

        $this->withToken($token)->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.email', 'bhakt@gmail.com');
    }

    public function test_second_login_finds_same_user_and_keeps_edited_name(): void
    {
        $this->fakeGoogle();
        $token = $this->postJson('/api/v1/auth/google', ['id_token' => 'abc'])->json('token');
        $this->withToken($token)->patchJson('/api/v1/me', ['name' => 'Raj', 'mobile' => '9876543210'])->assertOk();

        $this->postJson('/api/v1/auth/google', ['id_token' => 'abc'])
            ->assertOk()
            ->assertJsonPath('is_new', false)
            ->assertJsonPath('user.name', 'Raj')
            ->assertJsonPath('user.mobile', '9876543210');

        $this->assertSame(1, User::count());
    }

    public function test_two_accounts_may_enter_the_same_mobile(): void
    {
        User::factory()->create(['mobile' => '9876543210']);
        $this->fakeGoogle();
        $token = $this->postJson('/api/v1/auth/google', ['id_token' => 'abc'])->json('token');

        $this->withToken($token)->patchJson('/api/v1/me', ['mobile' => '9876543210'])->assertOk();
    }

    public function test_token_for_another_app_is_rejected(): void
    {
        $this->fakeGoogle(['aud' => 'someone-else.apps.googleusercontent.com']);
        $this->postJson('/api/v1/auth/google', ['id_token' => 'abc'])->assertUnprocessable();
        $this->assertSame(0, User::count());
    }

    public static function badTokens(): array
    {
        return [
            'email not verified' => [['email_verified' => 'false'], 200],
            'expired' => [['exp' => '1'], 200],
            'wrong issuer' => [['iss' => 'evil.example.com'], 200],
            'google says invalid' => [[], 400],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('badTokens')]
    public function test_bad_token_is_rejected(array $claims, int $status): void
    {
        $this->fakeGoogle($claims, $status);
        $this->postJson('/api/v1/auth/google', ['id_token' => 'abc'])->assertUnprocessable();
        $this->assertSame(0, User::count());
    }

    public function test_no_client_id_configured_rejects_login(): void
    {
        config(['sanatan.google.client_ids' => []]);
        $this->fakeGoogle();
        $this->postJson('/api/v1/auth/google', ['id_token' => 'abc'])->assertUnprocessable();
    }

    public function test_blocked_user_cannot_log_in(): void
    {
        $this->fakeGoogle();
        $this->postJson('/api/v1/auth/google', ['id_token' => 'abc'])->assertOk();
        User::query()->update(['status' => User::STATUS_BLOCKED]);

        $this->postJson('/api/v1/auth/google', ['id_token' => 'abc'])->assertForbidden();
    }
}
