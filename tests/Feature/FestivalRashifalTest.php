<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Festival;
use App\Models\Rashifal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FestivalRashifalTest extends TestCase
{
    use RefreshDatabase;

    private function festival(string $date, array $extra = []): Festival
    {
        return Festival::create(['name' => ['en' => 'Diwali', 'hi' => 'दीवाली'], 'date' => $date] + $extra);
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Start from no festivals (a migration adds real ones for the app).
        Festival::query()->delete();
    }

    private function admin(): Admin
    {
        return Admin::create(['name' => 'A', 'email' => 'a@example.com', 'password' => 'secret-pass-1']);
    }

    public function test_festivals_by_month_include_the_last_day_and_filter_type(): void
    {
        $this->festival('2026-11-01');
        $this->festival('2026-11-30', ['is_vrat' => true]);
        $this->festival('2026-12-01');
        $this->festival('2026-11-15', ['is_active' => false]);

        $this->getJson('/api/v1/festivals?month=11&year=2026')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/v1/festivals?month=11&year=2026&type=vrat')
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.date', '2026-11-30')->assertJsonMissingPath('data.0.about');
    }

    public function test_upcoming_starts_today_and_detail_has_ritual_steps(): void
    {
        $this->festival(today()->subDay()->toDateString());
        $f = $this->festival(today()->toDateString(), ['rituals' => ['en' => "Clean the house\n\nLight diyas\n"]]);
        $this->festival(today()->addDays(3)->toDateString());

        $this->getJson('/api/v1/festivals/upcoming?limit=5')->assertJsonCount(2, 'data')->assertJsonPath('data.0.id', $f->id);
        $this->getJson("/api/v1/festivals/{$f->id}", ['Accept-Language' => 'hi'])
            ->assertOk()
            ->assertJsonPath('data.name', 'दीवाली')
            ->assertJsonPath('data.rituals', ['Clean the house', 'Light diyas']);
    }

    public function test_rashifal_for_a_day_and_for_one_rashi_with_cache_refresh(): void
    {
        $r = Rashifal::create([
            'date' => '2026-11-08', 'rashi' => 4, 'overall' => ['en' => 'Good day', 'hi' => 'अच्छा दिन'],
            'stars' => ['overall' => 4], 'lucky_number' => 7, 'lucky_colour' => ['en' => 'Red'],
        ]);
        Rashifal::create(['date' => '2026-11-08', 'rashi' => 0, 'overall' => ['en' => 'Calm']]);

        $this->getJson('/api/v1/rashifal?date=2026-11-08')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/v1/rashifal?date=2026-11-08&rashi=4', ['Accept-Language' => 'hi'])
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.texts.overall', 'अच्छा दिन')
            ->assertJsonPath('data.0.texts.love', null)
            ->assertJsonPath('data.0.stars.overall', 4)
            ->assertJsonPath('data.0.lucky_colour', 'Red');

        $r->update(['overall' => ['en' => 'Changed']]);
        $this->getJson('/api/v1/rashifal?date=2026-11-08&rashi=4')->assertJsonPath('data.0.texts.overall', 'Changed');
        $this->getJson('/api/v1/rashifal?date=2026-11-09')->assertJsonCount(0, 'data');
    }

    public function test_admin_adds_festival_and_rashifal_and_duplicates_are_blocked(): void
    {
        $this->actingAs($this->admin(), 'admin');

        $this->post('/admin/festivals', [
            'date' => '2026-11-08', 'name' => ['en' => 'Diwali'], 'is_vrat' => '1', 'is_active' => '1',
            'rituals' => ['en' => "Step 1\nStep 2"],
        ])->assertRedirect('/admin/festivals?month=2026-11')->assertSessionHasNoErrors();
        $this->assertTrue(Festival::first()->is_vrat);

        $entry = ['date' => '2026-11-08', 'rashi' => 3, 'overall' => ['en' => 'Nice'], 'stars' => ['overall' => '5', 'love' => '']];
        $this->post('/admin/rashifal', $entry)->assertSessionHasNoErrors();
        $this->assertSame(['overall' => 5], Rashifal::first()->stars);

        $this->post('/admin/rashifal', $entry)->assertSessionHasErrors('rashi');
        $this->put('/admin/rashifal/'.Rashifal::first()->id, $entry)->assertSessionHasNoErrors();

        foreach (['/admin/festivals', '/admin/festivals/create', '/admin/rashifal', '/admin/rashifal/create?rashi=2',
            '/admin/rashifal/'.Rashifal::first()->id.'/edit', '/admin/festivals/'.Festival::first()->id.'/edit', '/admin'] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
