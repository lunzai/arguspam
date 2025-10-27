<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SessionAudit>
 */
class SessionAuditFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'org_id' => \App\Models\Org::factory(),
            'session_id' => \App\Models\Session::factory(),
            'request_id' => \App\Models\Request::factory(),
            'asset_id' => \App\Models\Asset::factory(),
            'user_id' => \App\Models\User::factory(),
            'query_text' => fake()->randomElement([
                'SELECT * FROM users WHERE active = 1',
                'UPDATE products SET price = price * 1.1',
                'INSERT INTO logs (message) VALUES (?)',
                'DELETE FROM temp_data WHERE created_at < ?',
            ]),
            'query_timestamp' => now(),
        ];
    }
}
