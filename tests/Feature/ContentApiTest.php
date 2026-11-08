<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Mantra;
use App\Models\Poster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentApiTest extends TestCase
{
    use RefreshDatabase;

    private function category(string $type, array $extra = []): Category
    {
        return Category::create(['type' => $type, 'name' => ['en' => 'Festival', 'hi' => 'त्योहार']] + $extra);
    }

    private function mantra(Category $c, array $extra = []): Mantra
    {
        return Mantra::create([
            'category_id' => $c->id, 'title' => ['en' => 'Gayatri Mantra', 'hi' => 'गायत्री मंत्र'],
            'deity' => ['en' => 'Maa Gayatri'], 'lyrics' => ['en' => 'Om bhur bhuva swaha'],
            'audio_path' => 'mantra-audio/g.mp3',
        ] + $extra);
    }

    public function test_categories_are_per_module_in_the_requested_language(): void
    {
        $this->category('poster');
        $this->category('poster', ['is_active' => false]);
        $this->category('mantra');

        $this->getJson('/api/v1/posters/categories', ['Accept-Language' => 'hi'])
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'त्योहार');

        // Gujarati is empty, so English is used.
        $this->getJson('/api/v1/mantras/categories', ['Accept-Language' => 'gu'])
            ->assertJsonPath('data.0.name', 'Festival');
    }

    public function test_category_cache_is_cleared_when_admin_edits(): void
    {
        $c = $this->category('poster');
        $this->getJson('/api/v1/posters/categories')->assertJsonPath('data.0.name', 'Festival');

        $c->update(['name' => ['en' => 'Daily']]);

        $this->getJson('/api/v1/posters/categories')->assertJsonPath('data.0.name', 'Daily');
    }

    public function test_posters_list_hides_inactive_and_future_ones_and_filters_by_category(): void
    {
        $a = $this->category('poster');
        $b = $this->category('poster');
        $base = ['title' => ['en' => 'Diwali'], 'image_path' => 'posters/x.jpg'];
        Poster::create($base + ['category_id' => $a->id]);
        Poster::create($base + ['category_id' => $b->id]);
        Poster::create($base + ['category_id' => $a->id, 'is_active' => false]);
        Poster::create($base + ['category_id' => $a->id, 'show_from' => today()->addDay()]);

        $this->getJson('/api/v1/posters')->assertOk()->assertJsonCount(2, 'data')->assertJsonStructure(['data', 'links', 'meta']);
        $this->getJson("/api/v1/posters?category={$a->id}")
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.frame', 'classic')
            ->assertJsonPath('data.0.image_url', fn ($url) => str_ends_with($url, '/storage/posters/x.jpg'));
    }

    public function test_mantra_list_is_light_and_detail_has_lyrics(): void
    {
        $m = $this->mantra($this->category('mantra'));

        $this->getJson('/api/v1/mantras')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Gayatri Mantra')
            ->assertJsonMissingPath('data.0.lyrics');

        $this->getJson("/api/v1/mantras/{$m->id}", ['Accept-Language' => 'hi'])
            ->assertOk()
            ->assertJsonPath('data.title', 'गायत्री मंत्र')
            ->assertJsonPath('data.lyrics', 'Om bhur bhuva swaha')
            ->assertJsonPath('data.audio_url', fn ($url) => str_ends_with($url, '/storage/mantra-audio/g.mp3'));
    }

    public function test_inactive_mantra_is_not_found(): void
    {
        $m = $this->mantra($this->category('mantra'), ['is_active' => false]);

        $this->getJson("/api/v1/mantras/{$m->id}")->assertNotFound()->assertExactJson(['message' => 'Not found.']);
    }
}
