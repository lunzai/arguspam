<?php

namespace Database\Factories;

use App\Models\Org;
use App\Models\OrgSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrgSetting>
 */
class OrgSettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'org_id' => Org::factory(),
            'key' => fake()->unique()->word(),
            'value' => ['enabled' => true],
        ];
    }
}
