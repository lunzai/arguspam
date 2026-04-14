<?php

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::of(fake()->sentence(3))
            ->chopEnd('.')
            ->headline();

        return [
            'name' => $name->kebab(),
            'description' => $name,
        ];
    }
}
