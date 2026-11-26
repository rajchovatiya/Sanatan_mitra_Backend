<?php

namespace Tests\Feature;

use App\Models\Suvichar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Home is cached for 10 minutes; admin changes must still show at once. */
class HomeCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_suvichar_shows_on_home_right_away(): void
    {
        $this->getJson('/api/v1/home', ['Accept-Language' => 'gu'])
            ->assertOk()->assertJsonPath('data.suvichar', null);

        $s = Suvichar::create([
            'date' => today()->toDateString(),
            'text' => ['en' => 'Seva is the highest dharma', 'gu' => 'સેવા એ જ પરમ ધર્મ છે'],
            'is_active' => true,
        ]);

        $this->getJson('/api/v1/home', ['Accept-Language' => 'gu'])
            ->assertJsonPath('data.suvichar.text', 'સેવા એ જ પરમ ધર્મ છે');

        $s->update(['is_active' => false]);
        $this->getJson('/api/v1/home', ['Accept-Language' => 'gu'])
            ->assertJsonPath('data.suvichar', null);
    }
}
