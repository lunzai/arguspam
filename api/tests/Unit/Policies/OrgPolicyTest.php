<?php

namespace Tests\Unit\Policies;

use App\Models\Org;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\OrgPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrgPolicyTest extends TestCase
{
    use RefreshDatabase;

    private OrgPolicy $policy;
    private User $user;
    private User $orgMember;
    private Org $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new OrgPolicy;
        $this->user = User::factory()->create();
        $this->orgMember = User::factory()->create();
        $this->org = Org::factory()->create();

        // Add orgMember to the org
        $this->orgMember->orgs()->attach($this->org);
    }

    public function test_view_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'org:viewany');

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_view_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
    }

    public function test_view_returns_true_when_user_has_view_any_permission(): void
    {
        $this->giveUserPermission($this->user, 'org:viewany');

        $this->assertTrue($this->policy->view($this->user, $this->org));
    }

    public function test_view_returns_true_when_user_is_org_member_with_view_permission(): void
    {
        $this->giveUserPermission($this->orgMember, 'org:view');

        $this->assertTrue($this->policy->view($this->orgMember, $this->org));
    }

    public function test_view_returns_false_when_user_is_org_member_without_view_permission(): void
    {
        $this->assertFalse($this->policy->view($this->orgMember, $this->org));
    }

    public function test_view_returns_false_when_user_is_not_org_member_and_lacks_view_any_permission(): void
    {
        $this->giveUserPermission($this->user, 'org:view');

        $this->assertFalse($this->policy->view($this->user, $this->org));
    }

    public function test_create_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'org:create');

        $this->assertTrue($this->policy->create($this->user));
    }

    public function test_create_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->create($this->user));
    }

    public function test_update_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'org:updateany');

        $this->assertTrue($this->policy->updateAny($this->user));
    }

    public function test_update_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->updateAny($this->user));
    }

    public function test_delete_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'org:deleteany');

        $this->assertTrue($this->policy->deleteAny($this->user));
    }

    public function test_delete_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->deleteAny($this->user));
    }

    public function test_restore_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'org:restoreany');

        $this->assertTrue($this->policy->restoreAny($this->user));
    }

    public function test_restore_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->restoreAny($this->user));
    }

    public function test_view_with_multiple_orgs(): void
    {
        // Create another org
        $anotherOrg = Org::factory()->create();

        // User is member of first org but not second
        $this->giveUserPermission($this->orgMember, 'org:view');

        // Should be able to view org they belong to
        $this->assertTrue($this->policy->view($this->orgMember, $this->org));

        // Should not be able to view org they don't belong to
        $this->assertFalse($this->policy->view($this->orgMember, $anotherOrg));
    }

    public function test_user_with_multiple_org_memberships(): void
    {
        // Create another org and add user to it
        $anotherOrg = Org::factory()->create();
        $this->orgMember->orgs()->attach($anotherOrg);

        $this->giveUserPermission($this->orgMember, 'org:view');

        // Should be able to view both orgs they belong to
        $this->assertTrue($this->policy->view($this->orgMember, $this->org));
        $this->assertTrue($this->policy->view($this->orgMember, $anotherOrg));
    }

    public function test_user_with_view_any_permission_can_view_all_orgs(): void
    {
        $anotherOrg = Org::factory()->create();

        $this->giveUserPermission($this->user, 'org:viewany');

        // Should be able to view any org regardless of membership
        $this->assertTrue($this->policy->view($this->user, $this->org));
        $this->assertTrue($this->policy->view($this->user, $anotherOrg));
    }

    public function test_user_with_multiple_permissions_can_perform_corresponding_actions(): void
    {
        $this->giveUserPermission($this->user, 'org:viewany');
        $this->giveUserPermission($this->user, 'org:create');
        $this->giveUserPermission($this->user, 'org:updateany');
        $this->giveUserPermission($this->user, 'org:deleteany');
        $this->giveUserPermission($this->user, 'org:restoreany');

        $this->assertTrue($this->policy->viewAny($this->user));
        $this->assertTrue($this->policy->view($this->user, $this->org));
        $this->assertTrue($this->policy->create($this->user));
        $this->assertTrue($this->policy->updateAny($this->user));
        $this->assertTrue($this->policy->deleteAny($this->user));
        $this->assertTrue($this->policy->restoreAny($this->user));
    }

    private function giveUserPermission(User $user, string $permissionName): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => $permissionName],
            ['description' => ucfirst(str_replace(':', ' ', $permissionName))]
        );
        $role = Role::factory()->create();
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);
        $user->clearUserRolePermissionCache();
    }
}
