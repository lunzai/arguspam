<?php

namespace Tests\Integration\Controllers\Request;

use App\Enums\AssetAccessRole;
use App\Enums\DatabaseScope;
use App\Enums\RequestStatus;
use App\Enums\RiskRating;
use App\Models\Asset;
use App\Models\AssetAccessGrant;
use App\Models\Org;
use App\Models\Request as RequestModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RequestApproverControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $approver;
    private User $requester;
    private Org $org;
    private Asset $asset;
    private RequestModel $request;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->org = Org::factory()->create();
        $this->approver = User::factory()->create();
        $this->requester = User::factory()->create();
        $this->org->users()->attach([$this->approver->id, $this->requester->id]);
        $this->asset = Asset::factory()->create(['org_id' => $this->org->id]);

        // Create a submitted request with future end_datetime
        $this->request = RequestModel::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->requester->id,
            'status' => RequestStatus::SUBMITTED,
            'end_datetime' => now()->addHour(),
        ]);
    }

    private function giveApproverGrant(): void
    {
        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_id' => $this->approver->id,
            'user_group_id' => null,
            'role' => AssetAccessRole::APPROVER,
        ]);
    }

    private function validApprovalPayload(): array
    {
        return [
            'start_datetime' => $this->request->start_datetime->toDateTimeString(),
            'end_datetime' => now()->addHour()->toDateTimeString(),
            'scope' => DatabaseScope::READ_ONLY->value,
            'approver_note' => 'Approved after review.',
            'approver_risk_rating' => RiskRating::LOW->value,
        ];
    }

    // -------------------------------------------------------------------------
    // GET /requests/{id}/permissions — show
    // -------------------------------------------------------------------------

    public function test_show_returns_permissions_flags(): void
    {
        $this->giveUserPermission($this->approver, 'request:view');

        $this->actingAsWithOrg($this->approver, $this->org)
            ->getJson("/requests/{$this->request->id}/permissions")
            ->assertStatus(200)
            ->assertJsonStructure(['data' => ['canApprove', 'canCancel']]);
    }

    // -------------------------------------------------------------------------
    // POST /requests/{id}/approve — store
    // -------------------------------------------------------------------------

    public function test_store_approves_submitted_request(): void
    {
        $this->giveUserPermission($this->approver, 'request:approve');
        $this->giveApproverGrant();

        $this->actingAsWithOrg($this->approver, $this->org)
            ->postJson("/requests/{$this->request->id}/approve", $this->validApprovalPayload())
            ->assertStatus(200);

        $this->assertEquals(
            RequestStatus::APPROVED->value,
            $this->request->fresh()->status->value,
        );
    }

    public function test_store_returns_422_for_non_submitted_request(): void
    {
        $this->giveUserPermission($this->approver, 'request:approve');
        $this->giveApproverGrant();
        $pendingRequest = RequestModel::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'status' => RequestStatus::PENDING,
            'end_datetime' => now()->addHour(),
        ]);

        $this->actingAsWithOrg($this->approver, $this->org)
            ->postJson("/requests/{$pendingRequest->id}/approve", $this->validApprovalPayload())
            ->assertStatus(422);
    }

    public function test_store_returns_403_without_permission(): void
    {
        $this->actingAsWithOrg($this->approver, $this->org)
            ->postJson("/requests/{$this->request->id}/approve", $this->validApprovalPayload())
            ->assertStatus(403);
    }

    public function test_store_returns_403_without_approver_asset_grant(): void
    {
        $this->giveUserPermission($this->approver, 'request:approve');
        // No AssetAccessGrant given — approver doesn't have access to this asset

        $this->actingAsWithOrg($this->approver, $this->org)
            ->postJson("/requests/{$this->request->id}/approve", $this->validApprovalPayload())
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // PUT /requests/{id}/reject — update
    // -------------------------------------------------------------------------

    public function test_update_rejects_submitted_request(): void
    {
        $this->giveUserPermission($this->approver, 'request:reject');
        $this->giveApproverGrant();

        $this->actingAsWithOrg($this->approver, $this->org)
            ->putJson("/requests/{$this->request->id}/reject", [
                'start_datetime' => $this->request->start_datetime->toDateTimeString(),
                'end_datetime' => now()->addHour()->toDateTimeString(),
                'scope' => DatabaseScope::READ_ONLY->value,
                'approver_note' => 'Rejected: insufficient justification.',
                'approver_risk_rating' => RiskRating::HIGH->value,
            ])
            ->assertStatus(200);

        $this->assertEquals(
            RequestStatus::REJECTED->value,
            $this->request->fresh()->status->value,
        );
    }

    public function test_update_returns_403_without_permission(): void
    {
        $this->actingAsWithOrg($this->approver, $this->org)
            ->putJson("/requests/{$this->request->id}/reject", [
                'start_datetime' => $this->request->start_datetime->toDateTimeString(),
                'end_datetime' => now()->addHour()->toDateTimeString(),
                'scope' => DatabaseScope::READ_ONLY->value,
                'approver_note' => 'Rejected.',
                'approver_risk_rating' => RiskRating::LOW->value,
            ])
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // DELETE /requests/{id}/cancel — delete
    // -------------------------------------------------------------------------

    public function test_delete_cancels_request_by_requester(): void
    {
        $this->giveUserPermission($this->requester, 'request:cancel');

        $this->actingAsWithOrg($this->requester, $this->org)
            ->deleteJson("/requests/{$this->request->id}/cancel")
            ->assertStatus(200);

        $this->assertEquals(
            RequestStatus::CANCELLED->value,
            $this->request->fresh()->status->value,
        );
    }

    public function test_delete_returns_422_for_already_approved_request(): void
    {
        $this->giveUserPermission($this->requester, 'request:cancel');
        $approvedRequest = RequestModel::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->requester->id,
            'status' => RequestStatus::APPROVED,
            'end_datetime' => now()->addHour(),
        ]);

        $this->actingAsWithOrg($this->requester, $this->org)
            ->deleteJson("/requests/{$approvedRequest->id}/cancel")
            ->assertStatus(422);
    }

    public function test_delete_returns_403_without_permission(): void
    {
        $this->actingAsWithOrg($this->approver, $this->org)
            ->deleteJson("/requests/{$this->request->id}/cancel")
            ->assertStatus(403);
    }
}
