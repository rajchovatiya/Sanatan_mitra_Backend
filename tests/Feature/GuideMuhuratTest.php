<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Guide;
use App\Models\MuhuratDate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuideMuhuratTest extends TestCase
{
    use RefreshDatabase;

    public function test_guides_list_and_detail_with_facts_and_points(): void
    {
        $ruby = Guide::create([
            'module' => 'gems', 'title' => ['en' => 'Ruby'], 'subtitle' => ['en' => 'Manikya'],
            'facts' => ['en' => "Planet: Surya (Sun)\nDay: Sunday\nNo colon here"],
            'points' => ['en' => "Confidence\n\nLeadership"],
        ]);
        Guide::create(['module' => 'tarot', 'title' => ['en' => 'The Fool']]);

        $this->getJson('/api/v1/guides/gems')->assertOk()->assertJsonCount(1, 'data')->assertJsonMissingPath('data.0.facts');
        $this->getJson('/api/v1/guides/unknown')->assertNotFound();
        $this->getJson("/api/v1/guides/tarot/{$ruby->id}")->assertNotFound();
        $this->getJson("/api/v1/guides/gems/{$ruby->id}")
            ->assertJsonPath('data.facts.0', ['label' => 'Planet', 'value' => 'Surya (Sun)'])
            ->assertJsonPath('data.facts.2', ['label' => 'No colon here', 'value' => ''])
            ->assertJsonPath('data.points', ['Confidence', 'Leadership']);
    }

    public function test_muhurat_types_and_dates_for_a_year(): void
    {
        $vivah = Category::create(['type' => 'muhurat', 'name' => ['en' => 'Vivah'], 'description' => ['en' => 'Marriage dates']]);
        MuhuratDate::create(['category_id' => $vivah->id, 'date' => '2026-12-31', 'start_time' => '07:15', 'note' => ['en' => 'Rohini']]);
        MuhuratDate::create(['category_id' => $vivah->id, 'date' => '2027-01-05']);
        MuhuratDate::create(['category_id' => $vivah->id, 'date' => '2026-05-01', 'is_active' => false]);

        $this->getJson('/api/v1/muhurat/categories')->assertJsonPath('data.0.description', 'Marriage dates');
        $this->getJson("/api/v1/muhurat?category={$vivah->id}&year=2026")
            ->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.start_time', '07:15')->assertJsonPath('data.0.note', 'Rohini');

        $poster = Category::create(['type' => 'poster', 'name' => ['en' => 'P']]);
        $this->getJson("/api/v1/muhurat?category={$poster->id}")->assertNotFound();
    }

    public function test_admin_screens(): void
    {
        $this->actingAs(Admin::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'secret-pass-1']), 'admin');

        $this->post('/admin/categories', ['type' => 'muhurat', 'name' => ['en' => 'Griha Pravesh'], 'description' => ['en' => 'New home'], 'is_active' => '1'])
            ->assertSessionHasNoErrors();
        $type = Category::first();
        $this->post('/admin/muhurat', ['category_id' => $type->id, 'date' => '2026-11-20', 'start_time' => '09:00', 'is_active' => '1'])
            ->assertSessionHasNoErrors();
        $this->delete("/admin/categories/{$type->id}")->assertSessionHasErrors('category');

        $this->post('/admin/guides', ['module' => 'rudraksha', 'title' => ['en' => '5 Mukhi'], 'is_active' => '1'])->assertSessionHasNoErrors();

        foreach (['/admin/muhurat', '/admin/muhurat/create', '/admin/muhurat/'.MuhuratDate::first()->id.'/edit',
            '/admin/guides?module=rudraksha', '/admin/guides/create', '/admin/guides/'.Guide::first()->id.'/edit',
            '/admin/categories?type=muhurat', "/admin/categories/{$type->id}/edit"] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
