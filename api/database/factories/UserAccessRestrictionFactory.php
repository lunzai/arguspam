<?php

namespace Database\Factories;

use App\Enums\AccessRestrictionType;
use App\Enums\Status;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserAccessRestriction>
 */
class UserAccessRestrictionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(AccessRestrictionType::cases()),
            'value' => $this->generateValueForType(AccessRestrictionType::IP_ADDRESS),
            'status' => Status::ACTIVE,
        ];
    }

    /**
     * Generate appropriate value array for the given restriction type
     */
    private function generateValueForType(AccessRestrictionType $type): array
    {
        return match ($type) {
            AccessRestrictionType::IP_ADDRESS => [
                'allowed_ips' => ['127.0.0.1', '192.168.1.0/24'],
            ],
            AccessRestrictionType::TIME_WINDOW => [
                'days' => [1, 2, 3, 4, 5], // Monday to Friday
                'start_time' => '09:00',
                'end_time' => '17:00',
                'timezone' => 'UTC',
            ],
            AccessRestrictionType::COUNTRY => [
                'allowed_countries' => ['US', 'CA'],
            ],
        };
    }

    /**
     * Create restriction with IP address type
     */
    public function ipAddress(array $allowedIps = ['127.0.0.1']): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AccessRestrictionType::IP_ADDRESS,
            'value' => ['allowed_ips' => $allowedIps],
        ]);
    }

    /**
     * Create restriction with time window type
     */
    public function timeWindow(array $days = [1, 2, 3, 4, 5], string $startTime = '09:00', string $endTime = '17:00', string $timezone = 'UTC'): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AccessRestrictionType::TIME_WINDOW,
            'value' => [
                'days' => $days,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'timezone' => $timezone,
            ],
        ]);
    }

    /**
     * Create restriction with location type
     */
    public function location(array $allowedCountries = ['US']): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AccessRestrictionType::COUNTRY,
            'value' => ['allowed_countries' => $allowedCountries],
        ]);
    }

    /**
     * Create inactive restriction
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::INACTIVE,
        ]);
    }

    /**
     * Create active restriction
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::ACTIVE,
        ]);
    }
}
