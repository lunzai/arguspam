<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Models\Org;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserGroup>
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
            'description' => fake()->optional()->sentence(),
            'status' => Status::ACTIVE->value,
            'created_by' => User::first()?->id ?? User::factory(),
            'updated_by' => User::first()?->id ?? User::factory(),
        ];
    }
}
