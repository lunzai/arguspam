<?php

namespace Database\Factories;

use App\Enums\AuditAction as AuditActionEnum;
use App\Models\Org;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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
        $org = Org::factory();
        $user = $org->users()->inRandomOrder()->first() ??
            User::factory()->has($org);
        $tables = DB::select('SHOW TABLES');
        $table = $tables[array_rand($tables)]->Tables_in_app;
        $tables = Arr::pluck($tables, 'name');

        return [
            'org_id' => $org,
            'user_id' => $user,
            'action_type' => fake()->randomElement(AuditActionEnum::cases()),
            'entity_type' => fake()->randomElement($tables),
            'entity_id' => fake()->randomNumber(),
            'description' => fake()->paragraph(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
