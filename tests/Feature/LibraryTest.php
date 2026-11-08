<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\GitaVerse;
use App\Models\LibraryItem;
use App\Models\Suvichar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_library_list_and_detail(): void
    {
        $i = LibraryItem::create(['type' => 'aarti', 'title' => ['en' => 'Om Jai Jagdish'], 'body' => ['en' => 'Full aarti text']]);
        LibraryItem::create(['type' => 'katha', 'title' => ['en' => 'Satyanarayan'], 'body' => ['en' => 'Katha']]);

        $this->getJson('/api/v1/library?type=aarti')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonMissingPath('data.0.body')->assertJsonStructure(['data', 'links', 'meta']);
        $this->getJson("/api/v1/library/{$i->id}")->assertJsonPath('data.body', 'Full aarti text');
    }

    public function test_gita_chapters_ship_with_the_schema_and_verses_are_listed(): void
    {
        GitaVerse::create(['chapter' => 2, 'verse' => 47, 'sanskrit' => 'कर्मण्येवाधिकारस्ते', 'meaning' => ['en' => 'Do your duty'], 'is_featured' => true]);

        $this->getJson('/api/v1/gita/chapters')->assertOk()->assertJsonCount(18, 'data')
            ->assertJsonPath('data.1.name', 'Sankhya Yoga')->assertJsonPath('data.17.verse_count', 78);
        $this->getJson('/api/v1/gita/chapters/2/verses')->assertJsonPath('data.0.verse', 47);
        $this->getJson('/api/v1/gita/chapters/19/verses')->assertNotFound();
        $this->getJson('/api/v1/gita/verse-of-the-day')->assertJsonPath('data.meaning', 'Do your duty');
    }

    public function test_suvichar_uses_dated_one_first_then_rotation(): void
    {
        $this->getJson('/api/v1/suvichar/today')->assertExactJson(['data' => null]);

        Suvichar::create(['text' => ['en' => 'Rotating']]);
        $this->getJson('/api/v1/suvichar/today')->assertJsonPath('data.text', 'Rotating');

        Suvichar::create(['date' => today(), 'text' => ['en' => 'Special day']]);
        $this->getJson('/api/v1/suvichar/today')->assertJsonPath('data.text', 'Special day');
    }

    public function test_admin_screens_and_verse_rules(): void
    {
        $this->actingAs(Admin::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'secret-pass-1']), 'admin');

        $this->post('/admin/library', ['type' => 'chalisa', 'title' => ['en' => 'Hanuman Chalisa'], 'body' => ['en' => 'Text'], 'is_active' => '1'])
            ->assertSessionHasNoErrors();

        $verse = ['chapter' => 12, 'verse' => 5, 'sanskrit' => 'श्लोक', 'meaning' => ['en' => 'Meaning']];
        $this->post('/admin/gita-verses', $verse)->assertSessionHasNoErrors();
        $this->post('/admin/gita-verses', $verse)->assertSessionHasErrors('verse');
        // Chapter 12 has only 20 verses.
        $this->post('/admin/gita-verses', ['verse' => 21] + $verse)->assertSessionHasErrors('verse');

        $this->put('/admin/gita/chapters/12', ['name' => ['en' => 'Bhakti Yoga', 'hi' => 'भक्ति योग']])->assertSessionHasNoErrors();
        $this->getJson('/api/v1/gita/chapters', ['Accept-Language' => 'hi'])->assertJsonPath('data.11.name', 'भक्ति योग');

        $this->post('/admin/suvichars', ['text' => ['en' => 'Seva'], 'is_active' => '1'])->assertSessionHasNoErrors();

        foreach (['/admin/library', '/admin/library/create', '/admin/library/'.LibraryItem::first()->id.'/edit',
            '/admin/gita', '/admin/gita/chapters/1/edit', '/admin/gita-verses?chapter=12', '/admin/gita-verses/create',
            '/admin/gita-verses/'.GitaVerse::first()->id.'/edit', '/admin/suvichars', '/admin/suvichars/create',
            '/admin/suvichars/'.Suvichar::first()->id.'/edit'] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
