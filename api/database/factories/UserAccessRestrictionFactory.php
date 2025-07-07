<?php

namespace Database\Factories;

use App\Enums\RestrictionType;
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
            'type' => $this->faker->randomElement(RestrictionType::cases()),
            'value' => $this->generateValueForType(RestrictionType::IP_ADDRESS),
            'status' => Status::ACTIVE,
        ];
    }

    /**
     * Generate appropriate value array for the given restriction type
     */
    private function generateValueForType(RestrictionType $type): array
    {
        return match ($type) {
            RestrictionType::IP_ADDRESS => [
                'allowed_ips' => ['127.0.0.1', '192.168.1.0/24'],
            ],
            RestrictionType::TIME_WINDOW => [
                'days' => [1, 2, 3, 4, 5], // Monday to Friday
                'start_time' => '09:00',
                'end_time' => '17:00',
                'timezone' => 'UTC',
            ],
            RestrictionType::LOCATION => [
                'allowed_countries' => ['US', 'CA'],
            ],
            RestrictionType::DEVICE => [
                'allowed_devices' => ['Chrome', 'Firefox'],
            ],
        };
    }

    /**
     * Create restriction with IP address type
     */
    public function ipAddress(array $allowedIps = ['127.0.0.1']): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => RestrictionType::IP_ADDRESS,
            'value' => ['allowed_ips' => $allowedIps],
        ]);
    }

    /**
     * Create restriction with time window type
     */
    public function timeWindow(array $days = [1, 2, 3, 4, 5], string $startTime = '09:00', string $endTime = '17:00', string $timezone = 'UTC'): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => RestrictionType::TIME_WINDOW,
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
            'type' => RestrictionType::LOCATION,
            'value' => ['allowed_countries' => $allowedCountries],
        ]);
    }

    /**
     * Create restriction with device type
     */
    public function device(array $allowedDevices = ['Chrome']): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => RestrictionType::DEVICE,
            'value' => ['allowed_devices' => $allowedDevices],
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
