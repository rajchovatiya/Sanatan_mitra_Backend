<?php

namespace Tests\Feature;

use App\Models\Festival;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** The festivals added by the Dec 2026 – Feb 2027 content migration. */
class FestivalSeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_three_months_of_festivals_are_there_in_all_languages(): void
    {
        $rows = Festival::query()->whereBetween('date', ['2026-12-01', '2027-02-28'])->get();
        $this->assertCount(15, $rows);
        foreach ($rows as $f) {
            foreach (['en', 'hi', 'gu'] as $lang) {
                $this->assertNotEmpty($f->name[$lang] ?? null, "{$f->date} name $lang");
                $this->assertNotEmpty($f->about[$lang] ?? null, "{$f->date} about $lang");
            }
        }
        $this->assertSame(6, $rows->where('is_vrat', true)->count());
    }

    public function test_upcoming_list_is_in_gujarati_and_starts_from_today(): void
    {
        Carbon::setTestNow('2026-11-26');

        $this->getJson('/api/v1/festivals/upcoming?limit=3', ['Accept-Language' => 'gu'])
            ->assertOk()
            ->assertJsonPath('data.0.date', '2026-12-04')
            ->assertJsonPath('data.0.name', 'ઉત્પન્ના એકાદશી')
            ->assertJsonPath('data.1.name', 'વિવાહ પંચમી');

        Carbon::setTestNow();
    }
}
