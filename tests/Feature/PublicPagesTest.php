<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Feedback;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_and_terms_show_default_text_while_admin_page_is_empty(): void
    {
        $this->get('/privacy')->assertOk()->assertSee('Privacy Policy')->assertSee('What we collect')
            ->assertSee('Delete account');
        $this->get('/terms')->assertOk()->assertSee('Terms')->assertSee('not professional, medical');
    }

    public function test_text_written_in_admin_wins(): void
    {
        Page::find('privacy')->update(['body' => ['en' => 'Our own policy text', 'hi' => 'हमारी नीति']]);

        $this->get('/privacy')->assertOk()->assertSee('Our own policy text')->assertDontSee('What we collect');
        $this->get('/privacy?lang=hi')->assertOk()->assertSee('हमारी नीति');
    }

    public function test_support_email_is_shown_when_set(): void
    {
        config(['app.support_email' => 'help@example.com']);

        $this->get('/privacy')->assertSee('help@example.com');
    }

    public function test_deletion_request_lands_in_feedback(): void
    {
        $this->get('/delete-account')->assertOk()->assertSee('Request deletion');

        $this->post('/delete-account', ['mobile' => '+91 98765 43210', 'note' => 'Please remove'])
            ->assertRedirect('/delete-account');

        $f = Feedback::sole();
        $this->assertSame('919876543210', $f->contact);
        $this->assertStringContainsString('ACCOUNT DELETION REQUEST', $f->message);
        $this->get('/delete-account')->assertSee('Request received');
    }

    public function test_deletion_request_needs_a_valid_number(): void
    {
        $this->post('/delete-account', ['mobile' => 'abc'])->assertSessionHasErrors('mobile');
        $this->assertSame(0, Feedback::count());
    }

    public function test_super_admin_deletes_an_app_user_with_their_data(): void
    {
        $user = User::factory()->create();
        $user->favorites()->create(['type' => 'mantra', 'item_id' => 1]);

        $this->actingAs(Admin::create(['name' => 'S', 'email' => 's@example.com', 'password' => 'secret-pass-1', 'role' => 'super_admin']), 'admin')
            ->delete("/admin/users/{$user->id}")
            ->assertRedirect('/admin/users');

        $this->assertModelMissing($user);
        $this->assertDatabaseCount('favorites', 0);
    }

    public function test_editor_cannot_delete_app_users(): void
    {
        $user = User::factory()->create();

        $this->actingAs(Admin::create(['name' => 'E', 'email' => 'e@example.com', 'password' => 'secret-pass-1', 'role' => 'editor']), 'admin')
            ->delete("/admin/users/{$user->id}")
            ->assertForbidden();

        $this->assertModelExists($user);
    }
}
