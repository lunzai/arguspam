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
    private Org $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new OrgPolicy;
        $this->user = User::factory()->create();
        $this->org = Org::factory()->create();
    }

    public function test_view_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'org:view');

        $this->assertTrue($this->policy->view($this->user));
    }

    public function test_view_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->view($this->user));
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

    public function test_update_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'org:updateany');

        $this->assertTrue($this->policy->update($this->user));
    }

    public function test_update_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->update($this->user));
    }

    public function test_delete_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'org:deleteany');

        $this->assertTrue($this->policy->delete($this->user));
    }

    public function test_delete_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->delete($this->user));
    }

    public function test_list_users_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'org:listusers');

        $this->assertTrue($this->policy->listUsers($this->user, $this->org));
    }

    public function test_list_users_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->listUsers($this->user, $this->org));
    }

    public function test_add_user_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'org:adduser');

        $this->assertTrue($this->policy->addUser($this->user, $this->org));
    }

    public function test_add_user_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->addUser($this->user, $this->org));
    }

    public function test_remove_user_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'org:removeuser');

        $this->assertTrue($this->policy->removeUser($this->user, $this->org));
    }

    public function test_remove_user_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->removeUser($this->user, $this->org));
    }

    public function test_list_user_groups_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'org:listusergroups');

        $this->assertTrue($this->policy->listUserGroups($this->user, $this->org));
    }

    public function test_list_user_groups_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->listUserGroups($this->user, $this->org));
    }

    public function test_user_with_multiple_permissions_can_perform_all_actions(): void
    {
        $this->giveUserPermission($this->user, 'org:view');
        $this->giveUserPermission($this->user, 'org:create');
        $this->giveUserPermission($this->user, 'org:updateany');
        $this->giveUserPermission($this->user, 'org:deleteany');
        $this->giveUserPermission($this->user, 'org:listusers');
        $this->giveUserPermission($this->user, 'org:adduser');
        $this->giveUserPermission($this->user, 'org:removeuser');
        $this->giveUserPermission($this->user, 'org:listusergroups');

        $this->assertTrue($this->policy->view($this->user));
        $this->assertTrue($this->policy->create($this->user));
        $this->assertTrue($this->policy->update($this->user));
        $this->assertTrue($this->policy->delete($this->user));
        $this->assertTrue($this->policy->listUsers($this->user, $this->org));
        $this->assertTrue($this->policy->addUser($this->user, $this->org));
        $this->assertTrue($this->policy->removeUser($this->user, $this->org));
        $this->assertTrue($this->policy->listUserGroups($this->user, $this->org));
    }

    public function test_user_with_no_permissions_cannot_perform_any_actions(): void
    {
        $this->assertFalse($this->policy->view($this->user));
        $this->assertFalse($this->policy->create($this->user));
        $this->assertFalse($this->policy->update($this->user));
        $this->assertFalse($this->policy->delete($this->user));
        $this->assertFalse($this->policy->listUsers($this->user, $this->org));
        $this->assertFalse($this->policy->addUser($this->user, $this->org));
        $this->assertFalse($this->policy->removeUser($this->user, $this->org));
        $this->assertFalse($this->policy->listUserGroups($this->user, $this->org));
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
