<?php

namespace Database\Factories;

use App\Enums\RequestScope;
use App\Enums\RequestStatus;
use App\Enums\RiskRating;
use App\Models\Asset;
use App\Models\Org;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Request>
 */
class RequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDatetime = $this->faker->dateTimeBetween('now', '+1 week');
        $duration = $this->faker->randomElement([30, 60, 90, 120]);
        $endDatetime = Carbon::parse($startDatetime)->addMinutes($duration);

        return [
            'org_id' => Org::factory(),
            'asset_id' => Asset::factory(),
            'requester_id' => User::factory(),
            'start_datetime' => $startDatetime,
            'end_datetime' => $endDatetime,
            'duration' => $duration,
            'reason' => $this->faker->paragraph(),
            'intended_query' => $this->faker->paragraph(),
            'scope' => $this->faker->randomElement(RequestScope::cases()),
            'status' => RequestStatus::PENDING,
        ];
    }

    public function sensitiveData(): static
    {
        $isSensitiveData = $this->faker->boolean();

        return $this->state(fn (array $attributes) => [
            'is_access_sensitive_data' => $isSensitiveData,
            'sensitive_data_note' => $isSensitiveData ? fake()->paragraph() : null,
        ]);
    }

    public function approved(): static
    {
        $this->approverNodeAndRiskRating();

        return $this->state(fn (array $attributes) => [
            'status' => RequestStatus::APPROVED->value,
            'approved_by' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'approved_at' => fake()->dateTimeBetween($attributes['start_datetime'], $attributes['end_datetime']),
        ]);
    }

    public function rejected(): static
    {
        $this->approverNodeAndRiskRating();

        return $this->state(fn (array $attributes) => [
            'status' => RequestStatus::REJECTED->value,
            'rejected_by' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'rejected_at' => fake()->dateTimeBetween($attributes['start_datetime'], $attributes['end_datetime']),
        ]);
    }

    private function approverNodeAndRiskRating(): static
    {
        return $this->state(fn (array $attributes) => [
            'approver_note' => fake()->paragraph(),
            'approver_risk_rating' => fake()->randomElement(RiskRating::cases()),
        ]);
    }
}
