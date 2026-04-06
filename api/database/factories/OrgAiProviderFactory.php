<?php

namespace Database\Factories;

use App\Models\Org;
use App\Models\OrgAiProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrgAiProvider>
 */
class OrgAiProviderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'org_id' => Org::factory(),
            'sort_order' => 0,
            'driver' => 'openai',
            'label' => 'primary',
            'options' => null,
            'api_key' => 'sk-test-'.fake()->sha256(),
            'is_enabled' => true,
        ];
    }
}
