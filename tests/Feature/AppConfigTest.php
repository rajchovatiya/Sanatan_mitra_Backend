<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_defaults_are_returned_and_saved_settings_override_them(): void
    {
        $this->getJson('/api/v1/app/config')
            ->assertOk()
            ->assertJsonPath('data.maintenance', false)
            ->assertJsonPath('data.features.panchang', true);

        Setting::create(['key' => 'maintenance', 'value' => true]);
        Setting::create(['key' => 'android_min_version', 'value' => '1.2.0']);

        $this->getJson('/api/v1/app/config')
            ->assertJsonPath('data.maintenance', true)
            ->assertJsonPath('data.android_min_version', '1.2.0');
    }

    public function test_unknown_api_route_gives_plain_json_404(): void
    {
        $this->getJson('/api/v1/nothing-here')->assertNotFound()->assertExactJson(['message' => 'Not found.']);
    }
}
