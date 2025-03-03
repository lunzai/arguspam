<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Models\Org;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserGroup>
 */
class UserGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'org_id' => Org::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'status' => Status::ACTIVE->value,
            'created_by' => User::first()?->id ?? User::factory(),
            'updated_by' => User::first()?->id ?? User::factory(),
        ];
    }
}
