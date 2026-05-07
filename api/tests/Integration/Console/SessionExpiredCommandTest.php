<?php

namespace Tests\Integration\Console;

use App\Models\AssetAccount;
use App\Models\Session;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SessionExpiredCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_command_exits_successfully(): void
    {
        $this->artisan('pam:session:expired')
            ->assertExitCode(0);
    }

    public function test_command_expires_scheduled_sessions_past_end_datetime(): void
    {
        $session = Session::factory()->create([
            'status' => 'scheduled',
            'scheduled_end_datetime' => now()->subMinutes(10),
            'scheduled_start_datetime' => now()->subHour(),
        ]);

        $this->artisan('pam:session:expired')
            ->assertExitCode(0);

        $this->assertDatabaseHas('sessions', [
            'id' => $session->id,
            'status' => 'expired',
        ]);
    }

    public function test_command_does_not_expire_scheduled_sessions_with_future_end_datetime(): void
    {
        $session = Session::factory()->create([
            'status' => 'scheduled',
            'scheduled_end_datetime' => now()->addHour(),
        ]);

        $this->artisan('pam:session:expired')
            ->assertExitCode(0);

        $this->assertDatabaseHas('sessions', [
            'id' => $session->id,
            'status' => 'scheduled',
        ]);
    }

    public function test_command_terminates_started_sessions_past_end_datetime(): void
    {
        $assetAccount = AssetAccount::factory()->create();
        $session = Session::factory()->create([
            'status' => 'started',
            'asset_id' => $assetAccount->asset_id,
            'asset_account_id' => $assetAccount->id,
            'scheduled_end_datetime' => now()->subMinutes(10),
            'start_datetime' => now()->subHour(),
        ]);
        Auth::loginUsingId($session->requester_id);

        $this->artisan('pam:session:expired')
            ->assertExitCode(0);

        $this->assertDatabaseHas('sessions', [
            'id' => $session->id,
            'status' => 'terminated',
        ]);
    }
}
