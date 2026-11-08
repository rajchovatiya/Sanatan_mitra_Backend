<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_needs_login(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_user_updates_only_allowed_fields(): void
    {
        $user = Sanctum::actingAs(User::factory()->create(['mobile' => '9876543210']));

        $this->patchJson('/api/v1/me', [
            'name' => 'Raj',
            'birth_date' => '1995-04-12',
            'birth_time' => '06:30',
            'rashi' => 4,
            'language' => 'hi',
            'business_name' => 'Raj Traders',
            'mobile' => '9000000000',
            'status' => 'blocked',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Raj')
            ->assertJsonPath('data.birth_date', '1995-04-12')
            ->assertJsonPath('data.birth_time', '06:30')
            ->assertJsonPath('data.rashi', 4)
            ->assertJsonPath('data.language', 'hi')
            ->assertJsonPath('data.mobile', '9876543210');

        $this->assertSame(User::STATUS_ACTIVE, $user->fresh()->status);
    }

    public function test_invalid_profile_values_are_rejected(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->patchJson('/api/v1/me', ['rashi' => 12, 'language' => 'fr', 'birth_time' => '25:00'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['rashi', 'language', 'birth_time']);
    }

    public function test_photo_upload_replaces_the_old_file(): void
    {
        Storage::fake('public');
        $user = Sanctum::actingAs(User::factory()->create());

        $this->post('/api/v1/me/photo', ['image' => UploadedFile::fake()->image('a.jpg', 300, 300)], ['Accept' => 'application/json'])
            ->assertOk();
        $first = $user->fresh()->photo_path;

        $url = $this->post('/api/v1/me/photo', ['image' => UploadedFile::fake()->image('b.png', 300, 300)], ['Accept' => 'application/json'])
            ->assertOk()
            ->json('data.photo_url');

        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($user->fresh()->photo_path);
        $this->assertStringContainsString('profile-photos/', $url);
    }

    public function test_non_image_upload_is_rejected(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->create());

        $this->post('/api/v1/me/business-logo', ['image' => UploadedFile::fake()->create('x.php', 10, 'text/plain')], ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('image');
    }

    public function test_device_token_moves_to_the_latest_user(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        Sanctum::actingAs($first);
        $this->postJson('/api/v1/me/devices', ['fcm_token' => 'tok', 'platform' => 'android'])->assertNoContent();

        Sanctum::actingAs($second);
        $this->postJson('/api/v1/me/devices', ['fcm_token' => 'tok', 'platform' => 'android', 'app_version' => '1.0.2'])
            ->assertNoContent();

        $this->assertSame(1, Device::count());
        $this->assertSame($second->id, Device::first()->user_id);
    }

    public function test_account_deletion_removes_user_data_and_files(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $token = $user->createToken('app')->plainTextToken;
        $user->forceFill(['photo_path' => UploadedFile::fake()->image('p.jpg')->store('profile-photos', 'public')])->save();
        $user->devices()->create(['fcm_token' => 'tok', 'platform' => 'ios']);
        $user->kundlis()->create([
            'name' => 'Self', 'gender' => 'male', 'birth_date' => '1990-01-01', 'birth_time' => '10:00',
            'birth_place' => 'Surat', 'lat' => 21.17, 'lng' => 72.83,
        ]);

        $this->withToken($token)->deleteJson('/api/v1/account')->assertNoContent();

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('devices', 0);
        $this->assertDatabaseCount('kundlis', 0);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        Storage::disk('public')->assertMissing($user->photo_path);
    }
}
