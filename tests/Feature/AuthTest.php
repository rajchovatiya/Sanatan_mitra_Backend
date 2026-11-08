<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Otp\OtpSender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakeOtpSender;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private FakeOtpSender $sms;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sms = new FakeOtpSender;
        $this->app->instance(OtpSender::class, $this->sms);
    }

    private function requestCode(string $mobile = '9876543210'): string
    {
        $this->postJson('/api/v1/auth/otp/send', ['mobile' => $mobile])->assertOk();

        return $this->sms->sent[$mobile];
    }

    public function test_new_user_logs_in_with_otp_and_token_works(): void
    {
        $code = $this->requestCode();

        $token = $this->postJson('/api/v1/auth/otp/verify', ['mobile' => '9876543210', 'code' => $code])
            ->assertOk()
            ->assertJsonPath('is_new', true)
            ->assertJsonPath('user.mobile', '9876543210')
            ->assertJsonPath('user.language', 'en')
            ->json('token');

        $this->withToken($token)->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.mobile', '9876543210');
    }

    public function test_existing_user_is_not_new(): void
    {
        User::factory()->create(['mobile' => '9876543210']);

        $this->postJson('/api/v1/auth/otp/verify', ['mobile' => '9876543210', 'code' => $this->requestCode()])
            ->assertOk()
            ->assertJsonPath('is_new', false);

        $this->assertSame(1, User::count());
    }

    public function test_wrong_code_is_rejected_and_code_works_only_once(): void
    {
        $code = $this->requestCode();
        $wrong = $code === '111111' ? '222222' : '111111';

        $this->postJson('/api/v1/auth/otp/verify', ['mobile' => '9876543210', 'code' => $wrong])
            ->assertUnprocessable()->assertJsonValidationErrors('code');

        $this->postJson('/api/v1/auth/otp/verify', ['mobile' => '9876543210', 'code' => $code])->assertOk();
        $this->postJson('/api/v1/auth/otp/verify', ['mobile' => '9876543210', 'code' => $code])->assertUnprocessable();
    }

    public function test_code_is_destroyed_after_too_many_wrong_attempts(): void
    {
        $code = $this->requestCode();
        $wrong = $code === '111111' ? '222222' : '111111';

        foreach (range(1, config('sanatan.otp.max_attempts')) as $ignored) {
            $this->postJson('/api/v1/auth/otp/verify', ['mobile' => '9876543210', 'code' => $wrong]);
        }

        $this->postJson('/api/v1/auth/otp/verify', ['mobile' => '9876543210', 'code' => $code])->assertUnprocessable();
    }

    public function test_invalid_mobile_is_rejected(): void
    {
        $this->postJson('/api/v1/auth/otp/send', ['mobile' => '12345'])
            ->assertUnprocessable()->assertJsonValidationErrors('mobile');
        $this->assertSame([], $this->sms->sent);
    }

    public function test_otp_send_is_rate_limited_per_mobile(): void
    {
        $this->requestCode();
        $this->postJson('/api/v1/auth/otp/send', ['mobile' => '9876543210'])->assertTooManyRequests();
    }

    public function test_blocked_user_cannot_log_in(): void
    {
        User::factory()->blocked()->create(['mobile' => '9876543210']);

        $this->postJson('/api/v1/auth/otp/verify', ['mobile' => '9876543210', 'code' => $this->requestCode()])
            ->assertForbidden();
    }

    public function test_code_is_only_returned_when_test_mode_allows_it(): void
    {
        $this->postJson('/api/v1/auth/otp/send', ['mobile' => '9876543210'])->assertJsonMissingPath('debug_code');

        config(['sanatan.otp.expose_code' => true]);
        $this->postJson('/api/v1/auth/otp/send', ['mobile' => '9123456789'])
            ->assertJsonPath('debug_code', $this->sms->sent['9123456789']);
    }

    public function test_logout_revokes_token_and_removes_device(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('app')->plainTextToken;
        $user->devices()->create(['fcm_token' => 'abc', 'platform' => 'android']);

        $this->withToken($token)->postJson('/api/v1/auth/logout', ['fcm_token' => 'abc'])->assertNoContent();

        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();
        $this->assertSame(0, $user->devices()->count());
    }
}
