<?php

namespace Tests\Unit\Policies;

use App\Models\Asset;
use App\Models\Org;
use App\Models\User;
use App\Policies\AssetPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetPolicyTest extends TestCase
{
    use RefreshDatabase;

    private AssetPolicy $policy;
    private User $user;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new AssetPolicy;
        $org = Org::factory()->create();
        $this->user = User::factory()->create();
        $this->asset = Asset::factory()->create(['org_id' => $org->id]);
    }

    // -------------------------------------------------------------------------
    // view
    // -------------------------------------------------------------------------

    public function test_view_returns_true_with_permission(): void
    {
        $this->giveUserPermission($this->user, 'asset:view');
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
        $this->giveUserPermission($this->user, 'asset:create');
        $this->assertTrue($this->policy->create($this->user));
    }

    public function test_create_returns_false_without_permission(): void
    {
        $this->assertFalse($this->policy->create($this->user));
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    public function test_update_returns_true_with_permission(): void
    {
        $this->giveUserPermission($this->user, 'asset:update');
        $this->assertTrue($this->policy->update($this->user, $this->asset));
    }

    public function test_update_returns_false_without_permission(): void
    {
        $this->assertFalse($this->policy->update($this->user, $this->asset));
    }

    // -------------------------------------------------------------------------
    // delete
    // -------------------------------------------------------------------------

    public function test_delete_returns_true_with_permission(): void
    {
        $this->giveUserPermission($this->user, 'asset:delete');
        $this->assertTrue($this->policy->delete($this->user, $this->asset));
    }

    public function test_delete_returns_false_without_permission(): void
    {
        $this->assertFalse($this->policy->delete($this->user, $this->asset));
    }

    // -------------------------------------------------------------------------
    // addAccessGrant / removeAccessGrant
    // -------------------------------------------------------------------------

    public function test_add_access_grant_returns_true_with_permission(): void
    {
        $this->giveUserPermission($this->user, 'asset:addaccessgrant');
        $this->assertTrue($this->policy->addAccessGrant($this->user, $this->asset));
    }

    public function test_remove_access_grant_returns_true_with_permission(): void
    {
        $this->giveUserPermission($this->user, 'asset:removeaccessgrant');
        $this->assertTrue($this->policy->removeAccessGrant($this->user, $this->asset));
    }
}
