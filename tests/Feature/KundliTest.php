<?php

namespace Tests\Feature;

use App\Models\Kundli;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class KundliTest extends TestCase
{
    use RefreshDatabase;

    private array $details = [
        'name' => 'Asha', 'relation' => 'Mother', 'gender' => 'female', 'birth_date' => '1970-08-15',
        'birth_time' => '05:45', 'birth_place' => 'Ahmedabad', 'lat' => 23.0225, 'lng' => 72.5714,
    ];

    public function test_user_saves_lists_updates_and_deletes_own_profiles(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $id = $this->postJson('/api/v1/me/kundlis', $this->details)
            ->assertCreated()
            ->assertJsonPath('data.birth_time', '05:45')
            ->assertJsonPath('data.tz_offset', 5.5)
            ->json('data.id');

        $this->getJson('/api/v1/me/kundlis')->assertOk()->assertJsonCount(1, 'data');

        $this->putJson("/api/v1/me/kundlis/{$id}", ['name' => 'Asha Ben'] + $this->details)
            ->assertOk()->assertJsonPath('data.name', 'Asha Ben');

        $this->deleteJson("/api/v1/me/kundlis/{$id}")->assertNoContent();
        $this->assertDatabaseCount('kundlis', 0);
    }

    public function test_other_users_profiles_are_hidden(): void
    {
        $other = User::factory()->create()->kundlis()->create($this->details);
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/me/kundlis')->assertJsonCount(0, 'data');
        $this->putJson("/api/v1/me/kundlis/{$other->id}", $this->details)
            ->assertNotFound()->assertExactJson(['message' => 'Not found.']);
        $this->deleteJson("/api/v1/me/kundlis/{$other->id}")->assertNotFound();
        $this->deleteJson('/api/v1/me/kundlis/99999')->assertNotFound()->assertExactJson(['message' => 'Not found.']);
        $this->assertDatabaseHas('kundlis', ['id' => $other->id]);
    }

    public function test_profile_limit_is_enforced(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());
        config(['sanatan.kundlis_per_user' => 2]);
        $user->kundlis()->createMany([$this->details, $this->details]);

        $this->postJson('/api/v1/me/kundlis', $this->details)->assertUnprocessable();
        $this->assertSame(2, Kundli::count());
    }

    public function test_required_birth_details_are_validated(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/me/kundlis', ['name' => 'X'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['gender', 'birth_date', 'birth_time', 'birth_place', 'lat', 'lng']);
    }
}
