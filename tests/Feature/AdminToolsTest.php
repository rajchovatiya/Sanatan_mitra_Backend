<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminToolsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $role = 'super_admin', string $email = 'super@example.com'): Admin
    {
        return Admin::create(['name' => ucfirst($role), 'email' => $email, 'password' => 'secret-pass-1', 'role' => $role]);
    }

    public function test_blocking_a_user_logs_them_out_and_unblock_restores(): void
    {
        $this->actingAs($this->admin('editor', 'ed@example.com'), 'admin');
        $user = User::factory()->create(['mobile' => '9876543210']);
        $user->createToken('app');

        $this->get('/admin/users?q=98765')->assertOk()->assertSee('9876543210');
        $this->get("/admin/users/{$user->id}")->assertOk();

        $this->patch("/admin/users/{$user->id}/block")->assertRedirect();
        $this->assertTrue($user->fresh()->isBlocked());
        $this->assertSame(0, $user->tokens()->count());

        $this->patch("/admin/users/{$user->id}/block");
        $this->assertFalse($user->fresh()->isBlocked());
    }

    public function test_editors_cannot_open_super_admin_pages(): void
    {
        $this->actingAs($this->admin('editor', 'ed@example.com'), 'admin');

        foreach (['/admin/settings', '/admin/admins', '/admin/audit'] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->get('/admin')->assertOk()->assertDontSee('App settings');
    }

    public function test_settings_change_the_app_config(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $this->get('/admin/settings')->assertOk();

        $this->put('/admin/settings', ['android_min_version' => '1.x'])->assertSessionHasErrors('android_min_version');
        $this->put('/admin/settings', [
            'android_min_version' => '1.2.0', 'ios_min_version' => '1.0.0', 'latest_version' => '1.3.0',
            'maintenance' => '1', 'maintenance_message' => 'Back soon', 'features' => ['panchang' => '1'],
        ])->assertSessionHasNoErrors();

        $this->getJson('/api/v1/app/config')
            ->assertJsonPath('data.android_min_version', '1.2.0')
            ->assertJsonPath('data.maintenance', true)
            ->assertJsonPath('data.features.panchang', true)
            ->assertJsonPath('data.features.tarot', false);
    }

    public function test_super_admin_manages_admins_but_cannot_lock_self_out(): void
    {
        $me = $this->admin();
        $this->actingAs($me, 'admin');

        $this->post('/admin/admins', ['name' => 'Editor', 'email' => 'ed@example.com', 'role' => 'editor', 'password' => 'short'])
            ->assertSessionHasErrors('password');
        $this->post('/admin/admins', ['name' => 'Editor', 'email' => 'ed@example.com', 'role' => 'editor', 'password' => 'long-enough-1', 'is_active' => '1'])
            ->assertSessionHasNoErrors();
        $this->assertTrue(Admin::firstWhere('email', 'ed@example.com')->is_active);

        $this->put("/admin/admins/{$me->id}", ['name' => 'Me', 'email' => $me->email, 'role' => 'editor', 'is_active' => '1'])
            ->assertSessionHasErrors('role');
        $this->assertTrue($me->fresh()->isSuper());

        $this->get('/admin/admins')->assertOk();
        $this->get("/admin/admins/{$me->id}/edit")->assertOk();
    }

    public function test_changes_are_audited_but_views_and_failures_are_not(): void
    {
        $this->actingAs($this->admin(), 'admin');

        $this->get('/admin/categories');
        $this->post('/admin/categories', ['type' => 'poster'])->assertSessionHasErrors();
        $this->post('/admin/categories', ['type' => 'poster', 'name' => ['en' => 'Good Morning'], 'is_active' => '1']);

        $this->assertSame(1, AdminAuditLog::count());
        $log = AdminAuditLog::first();
        $this->assertSame('admin.categories.store', $log->action);
        $this->assertSame('Good Morning', $log->subject);
        $this->get('/admin/audit')->assertOk()->assertSee('categories.store');
    }
}
