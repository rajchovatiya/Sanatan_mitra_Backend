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

    public function test_mantra_can_be_saved_without_lyrics(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $cat = Category::create(['type' => 'mantra', 'name' => ['en' => 'Mantra / Stuti']]);

        $this->post('/admin/mantras', [
            'category_id' => $cat->id, 'title' => ['en' => 'Om Namah Shivay 108'], 'deity' => ['en' => 'Shiv'],
            'lyrics' => ['en' => '', 'hi' => '', 'gu' => ''],
            'audio' => UploadedFile::fake()->create('o.mp3', 300, 'audio/mpeg'), 'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $m = Mantra::first();
        $this->assertNotNull($m);
        $this->getJson("/api/v1/mantras/{$m->id}")->assertOk()->assertJsonPath('data.lyrics', null);
    }

    public function test_admin_uploads_many_posters_at_once(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $cat = Category::create(['type' => 'poster', 'name' => ['en' => 'Good Morning']]);

        $this->get('/admin/posters/bulk')->assertOk()->assertSee('Upload many posters');

        $this->post('/admin/posters/bulk', [
            'category_id' => $cat->id,
            'images' => [
                UploadedFile::fake()->image('good_morning-01.jpg', 1080, 1350),
                UploadedFile::fake()->image('b.jpg', 1080, 1080),
                UploadedFile::fake()->image('c.png', 1080, 1080),
            ],
            'titles' => ['', 'Jai Shri Ram', ''],
            'footer_below' => '1',
            'is_active' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $posters = Poster::orderBy('id')->get();
        $this->assertCount(3, $posters);
        $this->assertSame(['Good morning 01', 'Jai Shri Ram', 'C'], $posters->map(fn ($p) => $p->title['en'])->all());
        foreach ($posters as $p) {
            $this->assertSame($cat->id, $p->category_id);
            $this->assertTrue($p->footer_below);
            $this->assertTrue($p->is_active);
            Storage::disk('public')->assertExists($p->image_path);
        }
    }

    public function test_bulk_upload_allows_at_most_ten_pictures(): void
    {
        $this->actingAs($this->admin(), 'admin');
        $cat = Category::create(['type' => 'poster', 'name' => ['en' => 'Daily']]);

        $this->post('/admin/posters/bulk', [
            'category_id' => $cat->id,
            'images' => array_map(fn ($i) => UploadedFile::fake()->image("p{$i}.jpg"), range(1, 11)),
        ])->assertSessionHasErrors('images');
        $this->assertSame(0, Poster::count());
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
