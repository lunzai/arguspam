<?php

namespace Tests\Unit\Policies;

use App\Enums\AssetAccessRole;
use App\Enums\RequestStatus;
use App\Models\Asset;
use App\Models\AssetAccessGrant;
use App\Models\Org;
use App\Models\Request as RequestModel;
use App\Models\User;
use App\Policies\RequestPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RequestPolicyTest extends TestCase
{
    use RefreshDatabase;

    private RequestPolicy $policy;
    private User $user;
    private Org $org;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->policy = new RequestPolicy;
        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        $this->org->users()->attach($this->user->id);
        $this->asset = Asset::factory()->create(['org_id' => $this->org->id]);

        // Set org context so canApproveAsset() scopes to the right org
        $this->app['request']->headers->set('x-organization-id', $this->org->id);
    }

    // -------------------------------------------------------------------------
    // view
    // -------------------------------------------------------------------------

    public function test_view_returns_true_with_permission(): void
    {
        $this->giveUserPermission($this->user, 'request:view');
        $this->assertTrue($this->policy->view($this->user));
    }

    public function test_view_returns_false_without_permission(): void
    {
        $this->assertFalse($this->policy->view($this->user));
    }

    // -------------------------------------------------------------------------
    // create
    // -------------------------------------------------------------------------

    public function test_create_returns_true_with_permission(): void
    {
        $this->giveUserPermission($this->user, 'request:create');
        $this->assertTrue($this->policy->create($this->user));
    }

    // -------------------------------------------------------------------------
    // approveAny
    // -------------------------------------------------------------------------

    public function test_approve_any_returns_true_with_permission(): void
    {
        $this->giveUserPermission($this->user, 'request:approveany');
        $this->assertTrue($this->policy->approveAny($this->user));
    }

    // -------------------------------------------------------------------------
    // approve
    // -------------------------------------------------------------------------

    public function test_approve_returns_true_via_approveany_shortcircuit(): void
    {
        $this->giveUserPermission($this->user, 'request:approveany');
        $request = RequestModel::factory()->create([
            'asset_id' => $this->asset->id,
            'status' => RequestStatus::SUBMITTED,
        ]);
        $this->assertTrue($this->policy->approve($this->user, $request));
    }

    public function test_approve_returns_true_with_approve_permission_and_approver_grant(): void
    {
        $this->giveUserPermission($this->user, 'request:approve');
        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_id' => $this->user->id,
            'user_group_id' => null,
            'role' => AssetAccessRole::APPROVER,
        ]);
        $request = RequestModel::factory()->create([
            'asset_id' => $this->asset->id,
            'status' => RequestStatus::SUBMITTED,
        ]);

        $this->assertTrue($this->policy->approve($this->user, $request));
    }

    public function test_approve_returns_false_with_approve_permission_but_no_grant(): void
    {
        $this->giveUserPermission($this->user, 'request:approve');
        $request = RequestModel::factory()->create([
            'asset_id' => $this->asset->id,
            'status' => RequestStatus::SUBMITTED,
        ]);

        $this->assertFalse($this->policy->approve($this->user, $request));
    }

    public function test_approve_returns_false_without_any_permission(): void
    {
        $request = RequestModel::factory()->create([
            'asset_id' => $this->asset->id,
            'status' => RequestStatus::SUBMITTED,
        ]);
        $this->assertFalse($this->policy->approve($this->user, $request));
    }

    // -------------------------------------------------------------------------
    // cancel
    // -------------------------------------------------------------------------

    public function test_cancel_returns_true_via_cancelany_shortcircuit(): void
    {
        $this->giveUserPermission($this->user, 'request:cancelany');
        $request = RequestModel::factory()->create([
            'asset_id' => $this->asset->id,
            'requester_id' => User::factory()->create()->id,
            'status' => RequestStatus::SUBMITTED,
        ]);
        $this->assertTrue($this->policy->cancel($this->user, $request));
    }

    public function test_cancel_returns_true_when_requester_with_permission(): void
    {
        $this->giveUserPermission($this->user, 'request:cancel');
        $request = RequestModel::factory()->create([
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
            'status' => RequestStatus::SUBMITTED,
        ]);
        $this->assertTrue($this->policy->cancel($this->user, $request));
    }

    public function test_cancel_returns_false_when_not_requester_with_permission(): void
    {
        $this->giveUserPermission($this->user, 'request:cancel');
        $otherUser = User::factory()->create();
        $request = RequestModel::factory()->create([
            'asset_id' => $this->asset->id,
            'requester_id' => $otherUser->id,
            'status' => RequestStatus::SUBMITTED,
        ]);
        $this->assertFalse($this->policy->cancel($this->user, $request));
    }
}
