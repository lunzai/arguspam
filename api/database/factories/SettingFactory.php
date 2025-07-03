<?php

namespace Database\Factories;

use App\Enums\SettingDataType;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    protected $model = Setting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->word(),
            'value' => $this->faker->word(),
            'data_type' => SettingDataType::STRING,
            'group' => $this->faker->word(),
            'label' => $this->faker->sentence(2),
            'description' => $this->faker->sentence(),
        ];
    }

    /**
     * Indicate that the setting should be a string type.
     */
    public function string(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type' => SettingDataType::STRING,
            'value' => $this->faker->word(),
        ]);
    }

    /**
     * Indicate that the setting should be an integer type.
     */
    public function integer(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type' => SettingDataType::INTEGER,
            'value' => (string) $this->faker->numberBetween(1, 1000),
        ]);
    }

    /**
     * Indicate that the setting should be a boolean type.
     */
    public function boolean(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type' => SettingDataType::BOOLEAN,
            'value' => $this->faker->boolean() ? 'true' : 'false',
        ]);
    }

    /**
     * Indicate that the setting should be a float type.
     */
    public function float(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type' => SettingDataType::FLOAT,
            'value' => (string) $this->faker->randomFloat(2, 0, 100),
        ]);
    }

    /**
     * Indicate that the setting should be a JSON type.
     */
    public function json(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_type' => SettingDataType::JSON,
            'value' => json_encode(['key' => $this->faker->word()]),
        ]);
    }

    /**
     * Indicate that the setting should be in a specific group.
     */
    public function group(string $group): static
    {
        return $this->state(fn (array $attributes) => [
            'group' => $group,
        ]);
    }

    /**
     * Indicate that the setting should have no group.
     */
    public function noGroup(): static
    {
        return $this->state(fn (array $attributes) => [
            'group' => null,
        ]);
    }
}