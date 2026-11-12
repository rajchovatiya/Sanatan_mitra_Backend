<?php

namespace Tests\Unit;

use App\Services\Astrology\ProkeralaMapper;
use Tests\TestCase;

/** Uses real Prokerala answers (tests/Fixtures/prokerala) to check the mapping into the app's format. */
class ProkeralaMapperTest extends TestCase
{
    private function fixture(string $name): array
    {
        return json_decode(file_get_contents(base_path("tests/Fixtures/prokerala/{$name}.json")), true);
    }

    public function test_panchang_uses_app_indexes(): void
    {
        $p = ProkeralaMapper::panchang($this->fixture('panchang'));

        // 12 Nov 2026, Ahmedabad: Shukla Tritiya, then Chaturthi; Jyeshtha, then Mula.
        $this->assertSame([2, 3], array_column($p['tithi'], 'index'));
        $this->assertSame([17, 18], array_column($p['nakshatra'], 'index'));
        $this->assertSame([6, 7], array_column($p['yoga'], 'index'));
        $this->assertSame(['Garaja', 'Vanija'], array_column($p['karana'], 'name'));
        $this->assertSame('2026-11-12T06:54:54+05:30', $p['sunrise']);
        $this->assertCount(2, $p['periods']['durmuhurat']);
        $this->assertArrayHasKey('varjyam', $p['periods']);
        $this->assertSame('2026-11-12T13:45:54+05:30', $p['periods']['rahu_kaal'][0]['start']);
    }

    public function test_kundli_is_small_and_complete(): void
    {
        $k = ProkeralaMapper::kundli($this->fixture('kundli'), $this->fixture('planets'));

        // Born 12 Apr 1995, 6:30 AM, Surat.
        $this->assertSame(11, $k['lagna_rashi']); // Meena
        $this->assertSame(4, $k['moon_rashi']); // Simha
        $this->assertSame(['index' => 9, 'pada' => 4], $k['nakshatra']); // Magha 4
        $this->assertSame('Kshatriya', $k['varna']);
        $this->assertSame('Vanchar', $k['vashya']);
        $this->assertSame('Mushak (Rat)', $k['yoni']);
        $this->assertSame('Rakshasa', $k['gana']);
        $this->assertSame('Antya', $k['nadi']);
        $this->assertSame('Surya', $k['rashi_lord']);
        $this->assertSame('Ruby', $k['lucky_gem']);
        $this->assertFalse($k['manglik']['has']);
        $this->assertNotEmpty($k['yogas']);

        $this->assertCount(10, $k['planets']);
        $lagna = collect($k['planets'])->firstWhere('name', 'Lagna');
        $this->assertSame(1, $lagna['house']);
        // Born near sunrise, so the Sun is in the Lagna sign (Meena); the Moon in Simha is 6 signs on.
        $this->assertSame(1, collect($k['planets'])->firstWhere('name', 'Surya')['house']);
        $this->assertSame(6, collect($k['planets'])->firstWhere('name', 'Chandra')['house']);

        $this->assertCount(9, $k['dasha']);
        $this->assertSame('Ketu', $k['dasha'][0]['planet']);
        $this->assertCount(9, $k['dasha'][0]['antardasha']);
        $this->assertArrayNotHasKey('pratyantardasha', $k['dasha'][0]['antardasha'][0]);
        $this->assertLessThan(20_000, strlen(json_encode($k)), 'The app gets a compact answer');
    }

    public function test_matching_lists_eight_kootas(): void
    {
        $m = ProkeralaMapper::matching($this->fixture('matching'));

        $this->assertSame(17.5, $m['total']);
        $this->assertSame(36, $m['max']);
        $this->assertSame(['Varna', 'Vashya', 'Tara', 'Yoni', 'Graha Maitri', 'Gana', 'Bhakoot', 'Nadi'], array_column($m['kootas'], 'name'));
        $this->assertSame(0.0, $m['kootas'][7]['obtained']);
        $this->assertSame(['rashi' => 4, 'nakshatra' => 9, 'pada' => 4, 'manglik' => false], $m['boy']);
    }
}
