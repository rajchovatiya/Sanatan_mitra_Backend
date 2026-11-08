<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mobile' => (string) fake()->unique()->numberBetween(6000000000, 9999999999),
            'name' => fake()->name(),
        ];
    }

    public function blocked(): static
    {
        return $this->state(fn () => ['status' => User::STATUS_BLOCKED]);
    }
}
