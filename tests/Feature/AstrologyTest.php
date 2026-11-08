<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Astrology\AstrologyProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Fakes\FakeAstrologyProvider;
use Tests\TestCase;

class AstrologyTest extends TestCase
{
    use RefreshDatabase;

    private array $birth = ['date' => '1995-04-12', 'time' => '06:30', 'lat' => 21.17, 'lng' => 72.83];

    private function fakeProvider(): FakeAstrologyProvider
    {
        $fake = new FakeAstrologyProvider;
        $this->app->instance(AstrologyProvider::class, $fake);

        return $fake;
    }

    public function test_panchang_answers_503_until_a_provider_is_set_up(): void
    {
        $this->getJson('/api/v1/astro/panchang?date=2026-11-08&lat=21.17&lng=72.83')
            ->assertStatus(503)
            ->assertExactJson(['message' => 'Astrology service is not available right now.']);
    }

    public function test_panchang_is_cached_for_nearby_places(): void
    {
        $fake = $this->fakeProvider();

        $this->getJson('/api/v1/astro/panchang?date=2026-11-08&lat=21.1702&lng=72.8311')
            ->assertOk()->assertJsonPath('data.tithi', 'Dwadashi');
        $this->getJson('/api/v1/astro/panchang?date=2026-11-08&lat=21.1698&lng=72.8309')->assertOk();
        $this->getJson('/api/v1/astro/panchang?date=2026-11-09&lat=21.17&lng=72.83')->assertOk();

        $this->assertSame(2, $fake->calls);
    }

    public function test_panchang_validates_input(): void
    {
        $this->getJson('/api/v1/astro/panchang?date=08-11-2026&lat=95')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['date', 'lat', 'lng']);
    }

    public function test_kundli_and_matching_need_login(): void
    {
        $this->postJson('/api/v1/astro/kundli', $this->birth)->assertUnauthorized();
        $this->postJson('/api/v1/astro/matching', ['boy' => $this->birth, 'girl' => $this->birth])->assertUnauthorized();
    }

    public function test_kundli_and_matching_are_cached(): void
    {
        $fake = $this->fakeProvider();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/astro/kundli', $this->birth)->assertOk()->assertJsonPath('data.lagna', 'Tula');
        $this->postJson('/api/v1/astro/kundli', $this->birth)->assertOk();
        $this->postJson('/api/v1/astro/matching', ['boy' => $this->birth, 'girl' => ['time' => '07:00'] + $this->birth])
            ->assertOk()->assertJsonPath('data.guna', 28);

        $this->assertSame(2, $fake->calls);
    }

    public function test_matching_validates_both_people(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/astro/matching', ['boy' => $this->birth])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['girl', 'girl.date', 'girl.time']);
    }
}
