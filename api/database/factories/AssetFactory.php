<?php

namespace Database\Factories;

use App\Enums\Dbms;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Org;
use App\Enums\Status;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Asset>
 */
class AssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::first()?->id ?? User::factory();
        return [
            'org_id' =>Org::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'status' => Status::ACTIVE->value,
            'host' => fake()->ipv4(),
            'port' => 3306,
            'dbms' => Dbms::MYSQL->value,
            'created_by' => $user,
            'updated_by' => $user,
        ];
    }
}
