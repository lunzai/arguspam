<?php

namespace Database\Factories;

use App\Enums\SessionStatus;
use App\Models\Asset;
use App\Models\Org;
use App\Models\Request;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Session>
 */
class SessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDatetime = now();
        $duration = fake()->numberBetween(1, 8);

        return [
            'org_id' => Org::factory(),
            'request_id' => Request::factory(),
            'asset_id' => Asset::factory(),
            'requester_id' => User::factory(),
            'approver_id' => User::factory(),
            'start_datetime' => $startDatetime,
            'scheduled_start_datetime' => $startDatetime,
            'scheduled_end_datetime' => $startDatetime->copy()->addHours($duration),
            'requested_duration' => $duration,
            'status' => SessionStatus::SCHEDULED,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}
