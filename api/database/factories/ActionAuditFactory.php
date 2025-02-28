<?php

namespace Database\Factories;

use App\Models\Org;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActionAudit>
 */
class ActionAuditFactory extends Factory
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
            'user_id' => User::factory(),
            'action' => $this->faker->word,
            'action_type' => $this->faker->word,
            'action_data' => $this->faker->word,
            'action_status' => $this->faker->word,
            'action_status_code' => $this->faker->word,
        ];
    }
}
