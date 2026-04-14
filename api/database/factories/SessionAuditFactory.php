<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\Org;
use App\Models\Request;
use App\Models\Session;
use App\Models\SessionAudit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SessionAudit>
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
            'org_id' => Org::factory(),
            'session_id' => Session::factory(),
            'request_id' => Request::factory(),
            'asset_id' => Asset::factory(),
            'user_id' => User::factory(),
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
