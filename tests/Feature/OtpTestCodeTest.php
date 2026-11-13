<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtpTestCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_test_code_logs_any_number_in(): void
    {
        config(['sanatan.otp.test_code' => '1234', 'sanatan.otp.length' => 4]);

        $this->postJson('/api/v1/auth/otp/send', ['mobile' => '9876543210'])
            ->assertOk()
            ->assertJsonPath('length', 4);

        $this->postJson('/api/v1/auth/otp/verify', ['mobile' => '9876543210', 'code' => '1234'])
            ->assertOk()
            ->assertJsonStructure(['token', 'is_new', 'user']);
    }

    public function test_a_wrong_code_is_refused_even_in_test_mode(): void
    {
        config(['sanatan.otp.test_code' => '1234', 'sanatan.otp.length' => 4]);

        $this->postJson('/api/v1/auth/otp/send', ['mobile' => '9876543210'])->assertOk();

        $this->postJson('/api/v1/auth/otp/verify', ['mobile' => '9876543210', 'code' => '4321'])
            ->assertStatus(422);
    }

    public function test_without_a_test_code_the_length_is_six(): void
    {
        $this->postJson('/api/v1/auth/otp/send', ['mobile' => '9876543210'])
            ->assertOk()
            ->assertJsonPath('length', 6);
    }
}
