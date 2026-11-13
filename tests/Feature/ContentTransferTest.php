<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Mantra;
use App\Models\Poster;
use App\Support\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContentTransferTest extends TestCase
{
    use RefreshDatabase;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('sanatan.media_disk'));
        $this->dir = storage_path('framework/testing/seed-content');
        File::deleteDirectory($this->dir);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dir);
        parent::tearDown();
    }

    public function test_export_then_import_on_an_empty_server_recreates_everything(): void
    {
        $posterCat = Category::create(['type' => 'poster', 'name' => ['en' => 'Good Morning']]);
        $mantraCat = Category::create(['type' => 'mantra', 'name' => ['en' => 'Chalisa']]);
        Media::disk()->put('posters/a.jpg', 'img');
        Media::disk()->put('mantra-audio/h.mp3', 'mp3');
        Poster::create(['category_id' => $posterCat->id, 'title' => ['en' => 'A'], 'image_path' => 'posters/a.jpg']);
        Mantra::create([
            'category_id' => $mantraCat->id, 'title' => ['en' => 'Hanuman Chalisa'], 'deity' => ['en' => 'Hanuman'],
            'lyrics' => ['en' => 'Jai Hanuman'], 'audio_path' => 'mantra-audio/h.mp3',
        ]);

        $this->artisan('content:export', ['--dir' => $this->dir])->assertSuccessful();

        // A fresh server: empty database and empty disk.
        Poster::query()->delete();
        Mantra::query()->delete();
        Category::query()->delete();
        Storage::fake(config('sanatan.media_disk'));

        $this->artisan('content:import', ['--dir' => $this->dir])->assertSuccessful();

        $this->assertSame(2, Category::count());
        $this->assertSame(1, Poster::count());
        $this->assertSame('Hanuman Chalisa', Mantra::first()->title['en']);
        $this->assertSame('Chalisa', Mantra::first()->category->name['en']);
        $this->assertSame('mp3', Media::disk()->get('mantra-audio/h.mp3'));
        $this->assertSame('img', Media::disk()->get('posters/a.jpg'));
    }

    public function test_import_twice_adds_nothing_the_second_time(): void
    {
        $cat = Category::create(['type' => 'poster', 'name' => ['en' => 'Festival']]);
        Media::disk()->put('posters/f.jpg', 'img');
        Poster::create(['category_id' => $cat->id, 'title' => ['en' => 'F'], 'image_path' => 'posters/f.jpg']);
        $this->artisan('content:export', ['--dir' => $this->dir])->assertSuccessful();

        $this->artisan('content:import', ['--dir' => $this->dir])->assertSuccessful();
        $this->artisan('content:import', ['--dir' => $this->dir])->assertSuccessful();

        $this->assertSame(1, Category::count());
        $this->assertSame(1, Poster::count());
    }

    public function test_import_without_a_pack_does_nothing(): void
    {
        $this->artisan('content:import', ['--dir' => $this->dir])->assertSuccessful();

        $this->assertSame(0, Category::count());
    }
}
