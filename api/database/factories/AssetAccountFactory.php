<?php

namespace Database\Factories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssetAccount>
 */
class AssetAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->bothify('???-####');
        $user = User::first()?->id ?? User::factory();
        return [
            'asset_id' => Asset::factory(),
            'name' => $name,
            'vault_path' => "assets/{$name}",
            'is_default' => false,
            'created_by' => $user,
            'updated_by' => $user,
        ];
    }
}
