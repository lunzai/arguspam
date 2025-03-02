<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\User;
use App\Models\UserGroup;
use App\Enums\AssetAccessRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssetAccessGrant>
 */
class AssetAccessGrantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'asset_id' => Asset::factory(),
            'role' => fake()->randomElement(AssetAccessRole::cases()),
        ];
    }

    public function user(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory(),
            'user_group_id' => null,
        ]);
    }

    public function userGroup(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'user_group_id' => UserGroup::factory(),
        ]);
    }

    public function approver(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => AssetAccessRole::APPROVER,
        ]);
    }

    public function requester(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => AssetAccessRole::REQUESTER,
        ]);
    }
}
