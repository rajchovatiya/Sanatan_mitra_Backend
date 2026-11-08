<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Feedback;
use App\Models\Mantra;
use App\Models\Temple;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FavoriteSearchSupportTest extends TestCase
{
    use RefreshDatabase;

    private function mantra(array $extra = []): Mantra
    {
        $c = Category::firstOrCreate(['type' => 'mantra'], ['name' => ['en' => 'Daily']]);

        return Mantra::create(['category_id' => $c->id, 'title' => ['en' => 'Gayatri Mantra', 'hi' => 'गायत्री मंत्र'],
            'deity' => ['en' => 'Maa Gayatri'], 'lyrics' => ['en' => 'Om']] + $extra);
    }

    public function test_user_saves_lists_and_removes_favourites(): void
    {
        $m = $this->mantra();
        $hidden = $this->mantra(['is_active' => false]);
        $t = Temple::create(['name' => ['en' => 'Somnath']]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/me/favorites', ['type' => 'mantra', 'item_id' => $m->id])->assertNoContent();
        $this->postJson('/api/v1/me/favorites', ['type' => 'mantra', 'item_id' => $m->id])->assertNoContent();
        $this->postJson('/api/v1/me/favorites', ['type' => 'temple', 'item_id' => $t->id])->assertNoContent();
        $this->postJson('/api/v1/me/favorites', ['type' => 'mantra', 'item_id' => $hidden->id])->assertUnprocessable();

        $this->getJson('/api/v1/me/favorites')->assertOk()->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.type', 'temple')->assertJsonPath('data.1.item.title', 'Gayatri Mantra');
        $this->getJson('/api/v1/me/favorites?type=mantra')->assertJsonCount(1, 'data');

        // An item turned off later disappears from the list.
        $t->update(['is_active' => false]);
        $this->getJson('/api/v1/me/favorites')->assertJsonCount(1, 'data');

        $this->deleteJson("/api/v1/me/favorites/mantra/{$m->id}")->assertNoContent();
        $this->getJson('/api/v1/me/favorites')->assertJsonCount(0, 'data');
    }

    public function test_search_matches_any_language_and_ignores_case(): void
    {
        $this->mantra();
        Temple::create(['name' => ['en' => 'Kashi Vishwanath']]);

        $this->getJson('/api/v1/search?q=gayatri')->assertOk()->assertJsonCount(1, 'data.mantras')->assertJsonCount(0, 'data.temples');
        $this->getJson('/api/v1/search?q=गायत्री')->assertJsonCount(1, 'data.mantras');
        $this->getJson('/api/v1/search?q=KASHI')->assertJsonCount(1, 'data.temples');
        // "%%" is taken literally, not as "match everything".
        $this->getJson('/api/v1/search?q=%25%25')->assertOk()->assertJsonCount(0, 'data.mantras');
        $this->getJson('/api/v1/search?q=a')->assertUnprocessable();
    }

    public function test_feedback_from_guest_and_user_and_pages(): void
    {
        $this->postJson('/api/v1/feedback', ['message' => 'Great app!', 'platform' => 'android'])->assertNoContent();
        $user = User::factory()->create();
        $this->withToken($user->createToken('app')->plainTextToken)
            ->postJson('/api/v1/feedback', ['message' => 'Please add more aartis'])->assertNoContent();
        $this->postJson('/api/v1/feedback', ['message' => 'x'])->assertUnprocessable();

        $this->assertSame([null, $user->id], Feedback::orderBy('id')->pluck('user_id')->all());

        $this->getJson('/api/v1/pages/privacy')->assertOk()->assertJsonPath('data.title', 'Privacy Policy');
        $this->getJson('/api/v1/pages/nothing')->assertNotFound();
    }

    public function test_admin_reads_feedback_and_edits_pages(): void
    {
        $this->actingAs(Admin::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'secret-pass-1']), 'admin');
        $f = Feedback::create(['message' => 'Hello there']);

        $this->get('/admin/feedback')->assertOk()->assertSee('Hello there');
        $this->patch("/admin/feedback/{$f->id}")->assertRedirect();
        $this->assertTrue($f->fresh()->is_seen);

        $this->put('/admin/pages/terms', ['title' => ['en' => 'Terms'], 'body' => ['en' => 'Be kind.', 'hi' => 'दयालु रहें']])
            ->assertSessionHasNoErrors();
        $this->getJson('/api/v1/pages/terms', ['Accept-Language' => 'hi'])->assertJsonPath('data.body', 'दयालु रहें');
        $this->get('/admin/pages')->assertOk();
        $this->get('/admin/pages/terms/edit')->assertOk();
    }
}
