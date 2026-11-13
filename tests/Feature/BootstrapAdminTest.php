<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BootstrapAdminTest extends TestCase
{
    use RefreshDatabase;

    private function setAdminEnv(?string $email, ?string $password): void
    {
        foreach (['FIRST_ADMIN_EMAIL' => $email, 'FIRST_ADMIN_PASSWORD' => $password, 'FIRST_ADMIN_NAME' => 'Owner'] as $k => $v) {
            if ($v === null) {
                putenv($k);
                unset($_ENV[$k], $_SERVER[$k]);
            } else {
                putenv("$k=$v");
                $_ENV[$k] = $_SERVER[$k] = $v;
            }
        }
    }

    protected function tearDown(): void
    {
        $this->setAdminEnv(null, null);
        parent::tearDown();
    }

    public function test_creates_the_first_admin_from_variables(): void
    {
        $this->setAdminEnv('owner@example.com', 'a-long-password');

        $this->artisan('admin:bootstrap')->assertSuccessful();

        $admin = Admin::where('email', 'owner@example.com')->first();
        $this->assertNotNull($admin);
        $this->assertSame('super_admin', $admin->role);
        $this->assertTrue(Hash::check('a-long-password', $admin->password));
    }

    public function test_does_nothing_when_an_admin_exists(): void
    {
        Admin::create(['email' => 'first@example.com', 'name' => 'First', 'role' => 'super_admin', 'password' => 'another-password']);
        $this->setAdminEnv('owner@example.com', 'a-long-password');

        $this->artisan('admin:bootstrap')->assertSuccessful();

        $this->assertSame(1, Admin::count());
    }

    public function test_does_nothing_without_variables(): void
    {
        $this->setAdminEnv(null, null);

        $this->artisan('admin:bootstrap')->assertSuccessful();

        $this->assertSame(0, Admin::count());
    }

    public function test_rejects_a_short_password(): void
    {
        $this->setAdminEnv('owner@example.com', 'short');

        $this->artisan('admin:bootstrap')->assertFailed();

        $this->assertSame(0, Admin::count());
    }
}
