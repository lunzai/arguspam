<?php

namespace Tests\Unit\Models;

use App\Enums\AssetAccountType;
use App\Enums\SessionStatus;
use App\Models\Asset;
use App\Models\AssetAccount;
use App\Models\Org;
use App\Models\Session;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SessionModelTest extends TestCase
{
    use RefreshDatabase;

    private Org $org;
    private Asset $asset;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        $this->org->users()->attach($this->user->id);
        $this->asset = Asset::factory()->create(['org_id' => $this->org->id]);
    }

    private function makeScheduledSession(array $overrides = []): Session
    {
        return Session::factory()->create(array_merge([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
            'status' => SessionStatus::SCHEDULED,
            'scheduled_start_datetime' => now()->subMinute(),
            'scheduled_end_datetime' => now()->addHour(),
        ], $overrides));
    }

    private function makeStartedSession(array $overrides = []): Session
    {
        $account = AssetAccount::factory()->create([
            'asset_id' => $this->asset->id,
            'type' => AssetAccountType::JIT,
            'is_active' => true,
        ]);

        return Session::factory()->create(array_merge([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
            'asset_account_id' => $account->id,
            'status' => SessionStatus::STARTED,
            'scheduled_start_datetime' => now()->subHour(),
            'scheduled_end_datetime' => now()->addHour(),
            'start_datetime' => now()->subMinutes(5),
        ], $overrides));
    }

    // -------------------------------------------------------------------------
    // canStart()
    // -------------------------------------------------------------------------

    public function test_can_start_returns_true_when_scheduled_inside_window(): void
    {
        $session = $this->makeScheduledSession([
            'scheduled_start_datetime' => now()->subMinute(),
            'scheduled_end_datetime' => now()->addHour(),
        ]);
        $this->assertTrue($session->canStart());
    }

    public function test_can_start_returns_false_before_scheduled_window(): void
    {
        $session = $this->makeScheduledSession([
            'scheduled_start_datetime' => now()->addHour(),
            'scheduled_end_datetime' => now()->addHours(2),
        ]);
        $this->assertFalse($session->canStart());
    }

    public function test_can_start_returns_false_after_scheduled_window(): void
    {
        $session = $this->makeScheduledSession([
            'scheduled_start_datetime' => now()->subHours(2),
            'scheduled_end_datetime' => now()->subHour(),
        ]);
        $this->assertFalse($session->canStart());
    }

    public function test_can_start_returns_false_when_already_started(): void
    {
        $session = $this->makeStartedSession();
        $this->assertFalse($session->canStart());
    }

    // -------------------------------------------------------------------------
    // canEnd()
    // -------------------------------------------------------------------------

    public function test_can_end_returns_true_when_started(): void
    {
        $session = $this->makeStartedSession();
        $this->assertTrue($session->canEnd());
    }

    public function test_can_end_returns_false_when_scheduled(): void
    {
        $session = $this->makeScheduledSession();
        $this->assertFalse($session->canEnd());
    }

    // -------------------------------------------------------------------------
    // canCancel()
    // -------------------------------------------------------------------------

    public function test_can_cancel_returns_true_when_scheduled_before_end(): void
    {
        $session = $this->makeScheduledSession([
            'scheduled_end_datetime' => now()->addHour(),
        ]);
        $this->assertTrue($session->canCancel());
    }

    public function test_can_cancel_returns_false_when_started(): void
    {
        $session = $this->makeStartedSession();
        $this->assertFalse($session->canCancel());
    }

    public function test_can_cancel_returns_false_when_scheduled_after_end(): void
    {
        $session = $this->makeScheduledSession([
            'scheduled_start_datetime' => now()->subHours(3),
            'scheduled_end_datetime' => now()->subHour(),
        ]);
        $this->assertFalse($session->canCancel());
    }

    // -------------------------------------------------------------------------
    // canTerminate()
    // -------------------------------------------------------------------------

    public function test_can_terminate_returns_true_when_started(): void
    {
        $session = $this->makeStartedSession();
        $this->assertTrue($session->canTerminate());
    }

    public function test_can_terminate_returns_false_when_scheduled(): void
    {
        $session = $this->makeScheduledSession();
        $this->assertFalse($session->canTerminate());
    }

    // -------------------------------------------------------------------------
    // canExpire()
    // -------------------------------------------------------------------------

    public function test_can_expire_returns_true_when_scheduled_after_end(): void
    {
        $session = $this->makeScheduledSession([
            'scheduled_start_datetime' => now()->subHours(2),
            'scheduled_end_datetime' => now()->subHour(),
        ]);
        $this->assertTrue($session->canExpire());
    }

    public function test_can_expire_returns_false_when_started(): void
    {
        $session = $this->makeStartedSession();
        $this->assertFalse($session->canExpire());
    }

    public function test_can_expire_returns_false_when_scheduled_before_end(): void
    {
        $session = $this->makeScheduledSession([
            'scheduled_end_datetime' => now()->addHour(),
        ]);
        $this->assertFalse($session->canExpire());
    }

    // -------------------------------------------------------------------------
    // expire() guard
    // -------------------------------------------------------------------------

    public function test_expire_throws_when_not_eligible(): void
    {
        $session = $this->makeStartedSession();
        $this->expectException(\Exception::class);
        $session->expire();
    }
}
