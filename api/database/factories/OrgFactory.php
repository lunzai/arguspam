<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Org>
 */
class OrgFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userId = User::first()?->id ?? User::factory();

        return [
            'name' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'status' => Status::ACTIVE->value,
            'created_by' => $userId,
            'updated_by' => $userId,
        ];
    }
}
