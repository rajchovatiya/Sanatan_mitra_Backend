<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Astrology\AstrologyProvider;
use App\Services\Astrology\ProkeralaProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProkeralaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(AstrologyProvider::class, new ProkeralaProvider('id', 'secret'));
    }

    public function test_panchang_logs_in_once_and_sends_lahiri_coordinates_and_time(): void
    {
        Http::fake([
            'api.prokerala.com/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            'api.prokerala.com/v2/*' => Http::response(['status' => 'ok', 'data' => ['vaara' => 'Thursday']]),
        ]);

        $this->getJson('/api/v1/astro/panchang?date=2026-11-12&lat=23.0225&lng=72.5714')
            ->assertOk()->assertJsonPath('data.vaara', 'Thursday');
        $this->getJson('/api/v1/astro/panchang?date=2026-11-13&lat=23.0225&lng=72.5714')->assertOk();

        Http::assertSentCount(3); // one token + two Panchang days
        Http::assertSent(fn (Request $r) => str_contains($r->url(), '/v2/astrology/panchang/advanced')
            && (int) $r['ayanamsa'] === 1
            && $r['coordinates'] === '23.02,72.57'
            && $r['datetime'] === '2026-11-12T06:00:00+05:30'
            && $r->hasHeader('Authorization', 'Bearer tok'));
    }

    public function test_matching_sends_both_birth_details(): void
    {
        Http::fake([
            'api.prokerala.com/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            'api.prokerala.com/v2/*' => Http::response(['data' => ['guna_milan' => ['total_points' => 28]]]),
        ]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/astro/matching', [
            'boy' => ['date' => '1995-04-12', 'time' => '06:30', 'lat' => 21.17, 'lng' => 72.83],
            'girl' => ['date' => '1997-02-01', 'time' => '18:05', 'lat' => 23.02, 'lng' => 72.57],
        ])->assertOk()->assertJsonPath('data.guna_milan.total_points', 28);

        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'kundli-matching')
            && $r['boy_dob'] === '1995-04-12T06:30:00+05:30'
            && $r['girl_coordinates'] === '23.02,72.57');
    }

    public function test_provider_errors_give_503_so_the_app_uses_its_own_calculation(): void
    {
        Http::fake([
            'api.prokerala.com/token' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
            'api.prokerala.com/v2/*' => Http::response(['status' => 'error'], 500),
        ]);

        $this->getJson('/api/v1/astro/panchang?date=2026-11-12&lat=23.02&lng=72.57')->assertStatus(503);
    }

    public function test_wrong_keys_give_503(): void
    {
        Http::fake(['api.prokerala.com/token' => Http::response(['error' => 'invalid_client'], 401)]);

        $this->getJson('/api/v1/astro/panchang?date=2026-11-12&lat=23.02&lng=72.57')->assertStatus(503);
    }
}
