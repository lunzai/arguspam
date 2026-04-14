<?php

namespace Tests\Integration\Console;

use App\Enums\RequestStatus;
use App\Models\Asset;
use App\Models\Org;
use App\Models\Request;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RequestExpiredCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    private function makeRequest(array $overrides = []): Request
    {
        $org = Org::factory()->create();
        $user = User::factory()->create();
        $asset = Asset::factory()->create(['org_id' => $org->id]);

        return Request::factory()->create(array_merge([
            'org_id' => $org->id,
            'asset_id' => $asset->id,
            'requester_id' => $user->id,
            'status' => RequestStatus::SUBMITTED,
            'start_datetime' => now()->subHours(2),
            'end_datetime' => now()->subHour(),
        ], $overrides));
    }

    public function test_command_exits_successfully(): void
    {
        $this->artisan('pam:request:expired')
            ->assertExitCode(0);
    }

    public function test_command_expires_submitted_requests_past_end_date(): void
    {
        $request = $this->makeRequest(['status' => RequestStatus::SUBMITTED]);

        $this->artisan('pam:request:expired')
            ->assertExitCode(0);

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'status' => RequestStatus::EXPIRED->value,
        ]);
    }

    public function test_command_does_not_expire_requests_with_future_end_date(): void
    {
        $request = $this->makeRequest([
            'status' => RequestStatus::SUBMITTED,
            'start_datetime' => now()->addHour(),
            'end_datetime' => now()->addHours(2),
        ]);

        $this->artisan('pam:request:expired')
            ->assertExitCode(0);

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'status' => RequestStatus::SUBMITTED->value,
        ]);
    }

    public function test_command_does_not_expire_approved_requests(): void
    {
        $request = $this->makeRequest(['status' => RequestStatus::APPROVED]);

        $this->artisan('pam:request:expired')
            ->assertExitCode(0);

        $this->assertDatabaseHas('requests', [
            'id' => $request->id,
            'status' => RequestStatus::APPROVED->value,
        ]);
    }
}
