<?php

namespace Tests\Integration\Controllers\Session;

use App\Enums\AssetAccountType;
use App\Enums\SessionStatus;
use App\Models\Asset;
use App\Models\AssetAccount;
use App\Models\Org;
use App\Models\Session;
use App\Models\User;
use App\Services\Jit\JitManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SessionRequesterControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $requester;
    private Org $org;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->org = Org::factory()->create();
        $this->requester = User::factory()->create();
        $this->org->users()->attach($this->requester->id);
        $this->asset = Asset::factory()->create(['org_id' => $this->org->id]);
    }

    /**
     * Create a SCHEDULED session with a window that includes now().
     */
    private function scheduledSession(): Session
    {
        return Session::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->requester->id,
            'status' => SessionStatus::SCHEDULED,
            'scheduled_start_datetime' => now()->subMinute(),
            'scheduled_end_datetime' => now()->addHour(),
        ]);
    }

    /**
     * Create a STARTED session with an asset account.
     */
    private function startedSession(): Session
    {
        $account = AssetAccount::factory()->create([
            'asset_id' => $this->asset->id,
            'type' => AssetAccountType::JIT,
            'is_active' => true,
        ]);
        $session = Session::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->requester->id,
            'asset_account_id' => $account->id,
            'status' => SessionStatus::STARTED,
            'scheduled_start_datetime' => now()->subHour(),
            'scheduled_end_datetime' => now()->addHour(),
            'start_datetime' => now()->subMinutes(10),
        ]);

        return $session;
    }

    // -------------------------------------------------------------------------
    // GET /sessions/{session}/permissions — show
    // -------------------------------------------------------------------------

    public function test_show_returns_permission_flags(): void
    {
        $this->giveUserPermission($this->requester, 'session:permission');
        $session = $this->scheduledSession();

        $this->actingAsWithOrg($this->requester, $this->org)
            ->getJson("/sessions/{$session->id}/permissions")
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['canStart', 'canEnd', 'canCancel', 'canTerminate', 'canRetrieveSecret']]);
    }

    // -------------------------------------------------------------------------
    // POST /sessions/{session}/start — store
    // -------------------------------------------------------------------------

    public function test_store_starts_scheduled_session(): void
    {
        $this->giveUserPermission($this->requester, 'session:start');
        $session = $this->scheduledSession();

        $account = AssetAccount::factory()->create([
            'asset_id' => $this->asset->id,
            'type' => AssetAccountType::JIT,
            'is_active' => true,
        ]);
        $this->mock(JitManager::class, fn ($m) => $m->shouldReceive('createAccount')->once()->andReturn($account)
        );

        $this->actingAsWithOrg($this->requester, $this->org)
            ->postJson("/sessions/{$session->id}/start")
            ->assertStatus(200);

        $this->assertEquals(SessionStatus::STARTED->value, $session->fresh()->status->value);
    }

    public function test_store_returns_422_for_non_scheduled_session(): void
    {
        $this->giveUserPermission($this->requester, 'session:start');
        $session = $this->startedSession();

        $this->actingAsWithOrg($this->requester, $this->org)
            ->postJson("/sessions/{$session->id}/start")
            ->assertStatus(422);
    }

    public function test_store_returns_422_when_outside_scheduled_window(): void
    {
        $this->giveUserPermission($this->requester, 'session:start');
        $session = Session::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->requester->id,
            'status' => SessionStatus::SCHEDULED,
            'scheduled_start_datetime' => now()->addHour(),
            'scheduled_end_datetime' => now()->addHours(2),
        ]);

        $this->actingAsWithOrg($this->requester, $this->org)
            ->postJson("/sessions/{$session->id}/start")
            ->assertStatus(422);
    }

    public function test_store_returns_403_without_permission(): void
    {
        $session = $this->scheduledSession();

        $this->actingAsWithOrg($this->requester, $this->org)
            ->postJson("/sessions/{$session->id}/start")
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // PUT /sessions/{session}/end — update
    // -------------------------------------------------------------------------

    public function test_update_ends_started_session(): void
    {
        $this->giveUserPermission($this->requester, 'session:end');
        $session = $this->startedSession();

        $this->actingAsWithOrg($this->requester, $this->org)
            ->putJson("/sessions/{$session->id}/end")
            ->assertStatus(200);

        $this->assertEquals(SessionStatus::ENDED->value, $session->fresh()->status->value);
    }

    public function test_update_returns_422_for_non_started_session(): void
    {
        $this->giveUserPermission($this->requester, 'session:end');
        $session = $this->scheduledSession();

        $this->actingAsWithOrg($this->requester, $this->org)
            ->putJson("/sessions/{$session->id}/end")
            ->assertStatus(422);
    }

    public function test_update_returns_403_without_permission(): void
    {
        $session = $this->startedSession();

        $this->actingAsWithOrg($this->requester, $this->org)
            ->putJson("/sessions/{$session->id}/end")
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // DELETE /sessions/{session}/cancel — delete
    // -------------------------------------------------------------------------

    public function test_delete_cancels_scheduled_session(): void
    {
        $this->giveUserPermission($this->requester, 'session:cancel');
        $session = $this->scheduledSession();

        $this->actingAsWithOrg($this->requester, $this->org)
            ->deleteJson("/sessions/{$session->id}/cancel")
            ->assertStatus(200);

        $this->assertEquals(SessionStatus::CANCELLED->value, $session->fresh()->status->value);
    }

    public function test_delete_returns_422_for_started_session(): void
    {
        $this->giveUserPermission($this->requester, 'session:cancel');
        $session = $this->startedSession();

        $this->actingAsWithOrg($this->requester, $this->org)
            ->deleteJson("/sessions/{$session->id}/cancel")
            ->assertStatus(422);
    }

    public function test_delete_returns_403_without_permission(): void
    {
        $session = $this->scheduledSession();

        $this->actingAsWithOrg($this->requester, $this->org)
            ->deleteJson("/sessions/{$session->id}/cancel")
            ->assertStatus(403);
    }
}
