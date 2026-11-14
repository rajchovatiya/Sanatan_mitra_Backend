<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Mantra;
use App\Models\Poster;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function admin(array $extra = []): Admin
    {
        return Admin::create(['name' => 'Raj', 'email' => 'raj@example.com', 'password' => 'secret-pass-1', 'role' => 'editor'] + $extra);
    }

    public function test_guests_are_sent_to_login_and_admin_can_log_in(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->admin();

        $this->post('/admin/login', ['email' => 'raj@example.com', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/admin/login', ['email' => 'raj@example.com', 'password' => 'secret-pass-1'])->assertRedirect('/admin');
        $this->get('/admin')->assertOk()->assertSee('Dashboard');
    }

    public function test_disabled_admin_cannot_log_in(): void
    {
        $this->admin(['is_active' => false]);

        $this->post('/admin/login', ['email' => 'raj@example.com', 'password' => 'secret-pass-1'])->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_app_user_token_cannot_open_admin(): void
    {
        $user = User::factory()->create();

        $this->withToken($user->createToken('app')->plainTextToken)->get('/admin/posters')->assertRedirect('/admin/login');
    }

    public function test_admin_uploads_category_poster_and_mantra_with_audio(): void
    {
        $this->actingAs($this->admin(), 'admin');

        $this->post('/admin/categories', [
            'type' => 'poster', 'name' => ['en' => 'Good Morning', 'hi' => 'सुप्रभात'], 'is_active' => '1',
            'icon' => UploadedFile::fake()->image('i.png'),
        ])->assertRedirect()->assertSessionHasNoErrors();
        $posterCat = Category::firstWhere('type', 'poster');
        Storage::disk('public')->assertExists($posterCat->icon_path);

        $this->post('/admin/posters', [
            'category_id' => $posterCat->id, 'title' => ['en' => 'Morning 1'], 'frame' => 'classic',
            'image' => UploadedFile::fake()->image('p.jpg', 1080, 1080), 'is_active' => '1',
        ])->assertSessionHasNoErrors();
        $poster = Poster::first();
        Storage::disk('public')->assertExists($poster->image_path);

        $mantraCat = Category::create(['type' => 'mantra', 'name' => ['en' => 'Daily']]);
        $this->post('/admin/mantras', [
            'category_id' => $mantraCat->id, 'title' => ['en' => 'Gayatri'], 'deity' => ['en' => 'Maa Gayatri'],
            'lyrics' => ['en' => 'Om bhur bhuva swaha'],
            'audio' => UploadedFile::fake()->create('g.mp3', 500, 'audio/mpeg'), 'is_active' => '1',
        ])->assertSessionHasNoErrors();
        Storage::disk('public')->assertExists(Mantra::first()->audio_path);

        // Replacing the image deletes the old file.
        $old = $poster->image_path;
        $this->put("/admin/posters/{$poster->id}", [
            'category_id' => $posterCat->id, 'title' => ['en' => 'Morning 1'], 'frame' => 'minimal',
            'image' => UploadedFile::fake()->image('new.jpg'),
        ])->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($old);
        $this->assertFalse($poster->fresh()->is_active);
    }

    public function test_footer_below_option_is_saved_and_sent_to_the_app(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $cat = Category::create(['type' => 'poster', 'name' => ['en' => 'Daily']]);

        $this->post('/admin/posters', [
            'category_id' => $cat->id, 'title' => ['en' => 'Full of text'], 'frame' => 'classic',
            'image' => UploadedFile::fake()->image('p.jpg', 1080, 1350), 'is_active' => '1', 'footer_below' => '1',
        ])->assertSessionHasNoErrors();
        $poster = Poster::first();
        $this->assertTrue($poster->footer_below);
        $this->getJson('/api/v1/posters')->assertJsonPath('data.0.footer_below', true);

        // Unticking it on edit turns it off.
        $this->put("/admin/posters/{$poster->id}", [
            'category_id' => $cat->id, 'title' => ['en' => 'Full of text'], 'frame' => 'classic', 'is_active' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertFalse($poster->fresh()->footer_below);
    }

    public function test_poster_must_use_a_poster_category_and_real_image(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $mantraCat = Category::create(['type' => 'mantra', 'name' => ['en' => 'Daily']]);

        $this->post('/admin/posters', [
            'category_id' => $mantraCat->id, 'title' => ['en' => 'X'], 'frame' => 'classic',
            'image' => UploadedFile::fake()->create('x.php', 5, 'text/plain'),
        ])->assertSessionHasErrors(['category_id', 'image']);
        $this->assertSame(0, Poster::count());
    }

    public function test_category_with_items_cannot_be_deleted(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $cat = Category::create(['type' => 'poster', 'name' => ['en' => 'Daily']]);
        Poster::create(['category_id' => $cat->id, 'title' => ['en' => 'X'], 'image_path' => 'posters/x.jpg']);

        $this->delete("/admin/categories/{$cat->id}")->assertSessionHasErrors('category');
        $this->assertModelExists($cat);
    }

    public function test_admin_pages_render(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $cat = Category::create(['type' => 'mantra', 'name' => ['en' => 'Daily']]);
        $m = Mantra::create(['category_id' => $cat->id, 'title' => ['en' => 'G'], 'deity' => ['en' => 'D'], 'lyrics' => ['en' => 'L']]);

        foreach (['/admin/categories', '/admin/categories/create', '/admin/posters', '/admin/posters/create',
            '/admin/mantras', '/admin/mantras/create', "/admin/mantras/{$m->id}/edit", "/admin/categories/{$cat->id}/edit"] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
