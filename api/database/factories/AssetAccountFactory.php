<?php

namespace Database\Factories;

use App\Enums\AssetAccountType;
use App\Models\Asset;
use App\Models\AssetAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssetAccount>
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
        $username = fake()->bothify('???-####');
        $user = User::first()?->id ?? User::factory();

        return [
            'asset_id' => Asset::factory(),
            'type' => AssetAccountType::ADMIN,
            'username' => 'root', // $username,
            'password' => 'root', // fake()->password(),
            'is_active' => true,
            'expires_at' => null,
            'created_by' => $user,
            'updated_by' => $user,
        ];
    }
}
