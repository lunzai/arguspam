<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    private UserPolicy $policy;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new UserPolicy;
        $this->user = User::factory()->create();
    }

    // -------------------------------------------------------------------------
    // viewAny
    // -------------------------------------------------------------------------

    public function test_view_any_returns_true_with_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:viewany');
        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_view_any_returns_false_without_permission(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
    }

    // -------------------------------------------------------------------------
    // view
    // -------------------------------------------------------------------------

    public function test_view_returns_true_via_view_any_shortcircuit(): void
    {
        $this->giveUserPermission($this->user, 'user:viewany');
        $other = User::factory()->create();
        $this->assertTrue($this->policy->view($this->user, $other));
    }

    public function test_view_returns_true_for_own_record_with_view_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:view');
        $this->assertTrue($this->policy->view($this->user, $this->user));
    }

    public function test_view_returns_false_for_other_user_with_view_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:view');
        $other = User::factory()->create();
        $this->assertFalse($this->policy->view($this->user, $other));
    }

    public function test_view_returns_false_without_any_permission(): void
    {
        $other = User::factory()->create();
        $this->assertFalse($this->policy->view($this->user, $other));
    }

    // -------------------------------------------------------------------------
    // create
    // -------------------------------------------------------------------------

    public function test_create_returns_true_with_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:create');
        $this->assertTrue($this->policy->create($this->user));
    }

    // -------------------------------------------------------------------------
    // updateAny
    // -------------------------------------------------------------------------

    public function test_update_any_returns_true_with_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:updateany');
        $this->assertTrue($this->policy->updateAny($this->user));
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    public function test_update_returns_true_via_updateany_shortcircuit(): void
    {
        $this->giveUserPermission($this->user, 'user:updateany');
        $other = User::factory()->create();
        $this->assertTrue($this->policy->update($this->user, $other));
    }

    public function test_update_returns_true_for_self_with_update_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:update');
        $this->assertTrue($this->policy->update($this->user, $this->user));
    }

    public function test_update_returns_false_for_other_with_update_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:update');
        $other = User::factory()->create();
        $this->assertFalse($this->policy->update($this->user, $other));
    }

    // -------------------------------------------------------------------------
    // deleteAny
    // -------------------------------------------------------------------------

    public function test_delete_any_returns_true_with_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:deleteany');
        $other = User::factory()->create();
        $this->assertTrue($this->policy->deleteAny($this->user, $other));
    }

    // -------------------------------------------------------------------------
    // changePassword
    // -------------------------------------------------------------------------

    public function test_change_password_returns_true_for_self_with_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:changepassword');
        $this->assertTrue($this->policy->changePassword($this->user, $this->user));
    }

    public function test_change_password_returns_false_for_other_with_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:changepassword');
        $other = User::factory()->create();
        $this->assertFalse($this->policy->changePassword($this->user, $other));
    }

    // -------------------------------------------------------------------------
    // resetPasswordAny
    // -------------------------------------------------------------------------

    public function test_reset_password_any_returns_true_with_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:resetpasswordany');
        $this->assertTrue($this->policy->resetPasswordAny($this->user));
    }
}
