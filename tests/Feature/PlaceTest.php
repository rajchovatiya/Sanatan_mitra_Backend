<?php

namespace Tests\Feature;

use App\Console\Commands\ImportPlaces;
use App\Http\Requests\Api\BirthDetailsRules;
use App\Models\Place;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PlaceTest extends TestCase
{
    use RefreshDatabase;

    private function place(int $id, string $name, int $population, array $extra = []): void
    {
        Place::create([
            'id' => $id, 'name' => $name, 'search_name' => Place::searchKey($name),
            'district' => 'Junagadh', 'state' => 'Gujarat', 'country_code' => 'IN',
            'lat' => 21.52, 'lng' => 70.46, 'timezone' => 'Asia/Kolkata', 'population' => $population,
            ...$extra,
        ]);
    }

    public function test_search_finds_villages_by_prefix_biggest_first(): void
    {
        Http::fake();
        $this->place(1, 'Junagadh', 320000);
        $this->place(2, 'Junavadar', 900, ['lat' => 21.6]);
        $this->place(3, 'Juni Chavand', 1500, ['lat' => 21.7]);
        $this->place(4, 'Rajkot', 1300000);

        $this->getJson('/api/v1/places?q=jun')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.name', 'Junagadh')
            ->assertJsonPath('data.1.name', 'Juni Chavand')
            ->assertJsonPath('data.0.district', 'Junagadh')
            ->assertJsonPath('data.0.timezone', 'Asia/Kolkata');

        Http::assertNothingSent();
    }

    public function test_openstreetmap_fills_in_when_our_list_has_nothing(): void
    {
        Http::fake(['nominatim.openstreetmap.org/*' => Http::response([[
            'lat' => '21.4', 'lon' => '70.3', 'name' => 'Tiny',
            'address' => ['village' => 'Tinygam', 'state_district' => 'Junagadh', 'state' => 'Gujarat'],
        ]])]);

        $this->getJson('/api/v1/places?q=tinygam')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Tinygam')
            ->assertJsonPath('data.0.timezone', 'Asia/Kolkata');

        // Second search comes from the cache.
        $this->getJson('/api/v1/places?q=tinygam')->assertOk();
        Http::assertSentCount(1);
    }

    public function test_nearest_place_to_a_gps_position(): void
    {
        $this->place(1, 'Junagadh', 320000, ['lat' => 21.5222, 'lng' => 70.4579]);
        $this->place(2, 'Vanthli', 20000, ['lat' => 21.4800, 'lng' => 70.3300]);

        $this->getJson('/api/v1/places/nearest?lat=21.49&lng=70.34')
            ->assertOk()->assertJsonPath('data.name', 'Vanthli');
        $this->getJson('/api/v1/places/nearest?lat=10&lng=10')
            ->assertOk()->assertJsonPath('data', null);
    }

    public function test_search_needs_two_letters(): void
    {
        $this->getJson('/api/v1/places?q=j')->assertStatus(422);
    }

    public function test_birth_timezone_uses_the_offset_of_that_date(): void
    {
        $this->assertSame(5.5, BirthDetailsRules::offsetAt('1990-08-15', '06:45', 'Asia/Kolkata'));
        $this->assertSame(-4.0, BirthDetailsRules::offsetAt('1990-07-01', '10:00', 'America/New_York'));
        $this->assertSame(-5.0, BirthDetailsRules::offsetAt('1990-01-01', '10:00', 'America/New_York'));
        $this->assertSame(-4.0, BirthDetailsRules::extract([
            'date' => '1990-07-01', 'time' => '10:00', 'lat' => 40.7, 'lng' => -74, 'tz' => 5.5, 'timezone' => 'America/New_York',
        ])['tz']);
    }

    public function test_import_reads_geonames_files(): void
    {
        $dir = storage_path('framework/testing/geonames');
        @mkdir($dir, 0775, true);
        file_put_contents("$dir/admin1CodesASCII.txt", "IN.09\tGujarat\tGujarat\t1270770\n");
        file_put_contents("$dir/admin2Codes.txt", "IN.09.1279\tJunagadh\tJunagadh\t1\n");
        $row = fn ($id, $name, $class, $code) => implode("\t", [$id, $name, $name, '', '21.5', '70.4', $class, $code, 'IN', '', '09', '1279', '', '', '800', '', '', 'Asia/Kolkata', '2020-01-01']);
        $zip = new \ZipArchive;
        $zip->open("$dir/IN.zip", \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('IN.txt', implode("\n", [$row(11, 'Majevadi', 'P', 'PPL'), $row(12, 'Girnar Hill', 'T', 'HLL'), $row(13, 'Old Fort', 'P', 'PPLQ')])."\n");
        $zip->close();

        $this->artisan(ImportPlaces::class, ['--dir' => $dir, '--no-world' => true])->assertSuccessful();

        $this->assertSame(1, Place::count());
        $p = Place::find(11);
        $this->assertSame('Junagadh', $p->district);
        $this->assertSame('Gujarat', $p->state);
        $this->assertSame('majevadi', $p->search_name);
    }
}
