<?php

namespace Tests\Integration\Controllers\Session;

use App\Enums\AssetAccessRole;
use App\Enums\AssetAccountType;
use App\Enums\SessionStatus;
use App\Models\Asset;
use App\Models\AssetAccessGrant;
use App\Models\AssetAccount;
use App\Models\Org;
use App\Models\Session;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SessionApproverControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $approver;
    private User $requester;
    private Org $org;
    private Asset $asset;
    private Session $session;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->org = Org::factory()->create();
        $this->approver = User::factory()->create();
        $this->requester = User::factory()->create();
        $this->org->users()->attach([$this->approver->id, $this->requester->id]);
        $this->asset = Asset::factory()->create(['org_id' => $this->org->id]);

        $account = AssetAccount::factory()->create([
            'asset_id' => $this->asset->id,
            'type' => AssetAccountType::JIT,
            'is_active' => true,
        ]);
        $this->session = Session::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->requester->id,
            'asset_account_id' => $account->id,
            'status' => SessionStatus::STARTED,
            'scheduled_start_datetime' => now()->subHour(),
            'scheduled_end_datetime' => now()->addHour(),
            'start_datetime' => now()->subMinutes(5),
        ]);
    }

    // -------------------------------------------------------------------------
    // DELETE /sessions/{session}/terminate
    // -------------------------------------------------------------------------

    public function test_delete_terminates_started_session_with_terminateany(): void
    {
        $this->giveUserPermission($this->approver, 'session:terminateany');

        $this->actingAsWithOrg($this->approver, $this->org)
            ->deleteJson("/sessions/{$this->session->id}/terminate")
            ->assertStatus(200);

        $this->assertEquals(SessionStatus::TERMINATED->value, $this->session->fresh()->status->value);
    }

    public function test_delete_terminates_started_session_with_approver_grant(): void
    {
        $this->giveUserPermission($this->approver, 'session:terminate');
        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_id' => $this->approver->id,
            'user_group_id' => null,
            'role' => AssetAccessRole::APPROVER,
        ]);

        $this->actingAsWithOrg($this->approver, $this->org)
            ->deleteJson("/sessions/{$this->session->id}/terminate")
            ->assertStatus(200);
    }

    public function test_delete_returns_422_for_non_started_session(): void
    {
        $this->giveUserPermission($this->approver, 'session:terminateany');
        $cancelledSession = Session::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'status' => SessionStatus::CANCELLED,
        ]);

        $this->actingAsWithOrg($this->approver, $this->org)
            ->deleteJson("/sessions/{$cancelledSession->id}/terminate")
            ->assertStatus(422);
    }

    public function test_delete_returns_403_without_permission(): void
    {
        $this->actingAsWithOrg($this->approver, $this->org)
            ->deleteJson("/sessions/{$this->session->id}/terminate")
            ->assertStatus(403);
    }

    public function test_delete_returns_403_with_terminate_but_no_approver_grant(): void
    {
        $this->giveUserPermission($this->approver, 'session:terminate');
        // No AssetAccessGrant for approver

        $this->actingAsWithOrg($this->approver, $this->org)
            ->deleteJson("/sessions/{$this->session->id}/terminate")
            ->assertStatus(403);
    }
}
