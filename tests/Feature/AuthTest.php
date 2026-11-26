<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** Mobile OTP login was removed; it must not let anyone in (it had a test code). */
    public function test_mobile_otp_login_no_longer_exists(): void
    {
        $this->postJson('/api/v1/auth/otp/send', ['mobile' => '9876543210'])->assertNotFound();
        $this->postJson('/api/v1/auth/otp/verify', ['mobile' => '9876543210', 'code' => '1234'])->assertNotFound();
        $this->assertSame(0, User::count());
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
