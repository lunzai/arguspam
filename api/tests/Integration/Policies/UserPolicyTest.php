<?php

namespace Tests\Unit\Policies;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    private UserPolicy $policy;
    private User $user;
    private User $targetUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new UserPolicy;
        $this->user = User::factory()->create();
        $this->targetUser = User::factory()->create();
    }

    public function test_view_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:viewany');

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_view_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
    }

    public function test_view_returns_true_when_user_has_view_any_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:viewany');

        $this->assertTrue($this->policy->view($this->user, $this->targetUser));
    }

    public function test_view_returns_true_when_viewing_own_profile_with_view_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:view');

        $this->assertTrue($this->policy->view($this->user, $this->user));
    }

    public function test_view_returns_false_when_viewing_own_profile_without_view_permission(): void
    {
        $this->assertFalse($this->policy->view($this->user, $this->user));
    }

    public function test_view_returns_false_when_viewing_other_user_without_view_any_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:view');

        $this->assertFalse($this->policy->view($this->user, $this->targetUser));
    }

    public function test_create_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:create');

        $this->assertTrue($this->policy->create($this->user));
    }

    public function test_create_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->create($this->user));
    }

    public function test_update_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:updateany');

        $this->assertTrue($this->policy->updateAny($this->user));
    }

    public function test_update_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->updateAny($this->user));
    }

    public function test_update_returns_true_when_user_has_update_any_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:updateany');

        $this->assertTrue($this->policy->update($this->user, $this->targetUser));
    }

    public function test_update_returns_true_when_updating_own_profile_with_update_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:update');

        $this->assertTrue($this->policy->update($this->user, $this->user));
    }

    public function test_update_returns_false_when_updating_own_profile_without_update_permission(): void
    {
        $this->assertFalse($this->policy->update($this->user, $this->user));
    }

    public function test_update_returns_false_when_updating_other_user_without_update_any_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:update');

        $this->assertFalse($this->policy->update($this->user, $this->targetUser));
    }

    public function test_delete_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:deleteany');

        $this->assertTrue($this->policy->deleteAny($this->user, $this->targetUser));
    }

    public function test_delete_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->deleteAny($this->user, $this->targetUser));
    }

    public function test_reset_password_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:resetpasswordany');

        $this->assertTrue($this->policy->resetPasswordAny($this->user));
    }

    public function test_reset_password_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->resetPasswordAny($this->user));
    }

    public function test_change_password_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:changepassword');

        $this->assertTrue($this->policy->changePassword($this->user));
    }

    public function test_change_password_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->changePassword($this->user));
    }

    public function test_enroll_two_factor_authentication_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:enrolltwofactorauthenticationany');

        $this->assertTrue($this->policy->enrollTwoFactorAuthenticationAny($this->user));
    }

    public function test_enroll_two_factor_authentication_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->enrollTwoFactorAuthenticationAny($this->user));
    }

    public function test_enroll_two_factor_authentication_returns_true_when_user_has_any_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:enrolltwofactorauthenticationany');

        $this->assertTrue($this->policy->enrollTwoFactorAuthentication($this->user, $this->targetUser));
    }

    public function test_enroll_two_factor_authentication_returns_true_when_enrolling_self_with_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:enrolltwofactorauthentication');

        $this->assertTrue($this->policy->enrollTwoFactorAuthentication($this->user, $this->user));
    }

    public function test_enroll_two_factor_authentication_returns_false_when_enrolling_other_without_any_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:enrolltwofactorauthentication');

        $this->assertFalse($this->policy->enrollTwoFactorAuthentication($this->user, $this->targetUser));
    }

    public function test_enroll_two_factor_authentication_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->enrollTwoFactorAuthentication($this->user, $this->user));
    }

    public function test_user_with_multiple_permissions_can_perform_corresponding_actions(): void
    {
        $this->giveUserPermission($this->user, 'user:viewany');
        $this->giveUserPermission($this->user, 'user:create');
        $this->giveUserPermission($this->user, 'user:updateany');
        $this->giveUserPermission($this->user, 'user:deleteany');
        $this->giveUserPermission($this->user, 'user:resetpasswordany');
        $this->giveUserPermission($this->user, 'user:changepassword');
        $this->giveUserPermission($this->user, 'user:enrolltwofactorauthenticationany');

        $this->assertTrue($this->policy->viewAny($this->user));
        $this->assertTrue($this->policy->view($this->user, $this->targetUser));
        $this->assertTrue($this->policy->create($this->user));
        $this->assertTrue($this->policy->updateAny($this->user));
        $this->assertTrue($this->policy->update($this->user, $this->targetUser));
        $this->assertTrue($this->policy->deleteAny($this->user, $this->targetUser));
        $this->assertTrue($this->policy->resetPasswordAny($this->user));
        $this->assertTrue($this->policy->changePassword($this->user));
        $this->assertTrue($this->policy->enrollTwoFactorAuthenticationAny($this->user));
        $this->assertTrue($this->policy->enrollTwoFactorAuthentication($this->user, $this->targetUser));
    }

    public function test_user_with_only_self_permissions_cannot_act_on_others(): void
    {
        $this->giveUserPermission($this->user, 'user:view');
        $this->giveUserPermission($this->user, 'user:update');
        $this->giveUserPermission($this->user, 'user:enrolltwofactorauthentication');

        // Can act on self
        $this->assertTrue($this->policy->view($this->user, $this->user));
        $this->assertTrue($this->policy->update($this->user, $this->user));
        $this->assertTrue($this->policy->enrollTwoFactorAuthentication($this->user, $this->user));

        // Cannot act on others
        $this->assertFalse($this->policy->view($this->user, $this->targetUser));
        $this->assertFalse($this->policy->update($this->user, $this->targetUser));
        $this->assertFalse($this->policy->enrollTwoFactorAuthentication($this->user, $this->targetUser));
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
