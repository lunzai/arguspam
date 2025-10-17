<?php

namespace Database\Factories;

use App\Enums\Dbms;
use App\Enums\Status;
use App\Models\Org;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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

        $dbSuffix = [
            'UAT', 'Staging', 'Prod', 'Live',
            'Private', 'Public', 'Dev', 'QA', 'Pre-Prod',
            'Test', 'Demo', 'Sandbox', 'Backup', 'Archived',
            'Finance', 'HR', 'IT', 'Marketing', 'Sales', 'Customer Support',
            'Engineering', 'Product', 'Legal', 'Ops', 'Security',
            'Analytics', 'Reporting', 'Monitoring',
        ];
        return [
            'org_id' => Org::factory(),
            'name' => fake()->bothify('?????-###').' - '.fake()->randomElement($dbSuffix),
            'description' => fake()->optional()->paragraph(),
            'status' => Status::ACTIVE->value,
            'host' => '127.0.0.1', // fake()->ipv4(),
            'port' => 3306,
            'dbms' => Dbms::MYSQL->value,
            'created_by' => $user,
            'updated_by' => $user,
        ];
    }
}
