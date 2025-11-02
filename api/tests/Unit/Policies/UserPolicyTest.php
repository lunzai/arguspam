<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\UserPolicy;
use Mockery;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_view_any_returns_true_when_user_has_permission(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasAnyPermission')
            ->once()
            ->with('user:viewany')
            ->andReturn(true);

        $policy = new UserPolicy;
        $this->assertTrue($policy->viewAny($user));
    }

    public function test_view_any_returns_false_when_user_lacks_permission(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasAnyPermission')
            ->once()
            ->with('user:viewany')
            ->andReturn(false);

        $policy = new UserPolicy;
        $this->assertFalse($policy->viewAny($user));
    }

    public function test_view_returns_true_when_user_has_view_any_permission(): void
    {
        $user = Mockery::mock(User::class);
        $model = Mockery::mock(User::class);
        $user->shouldReceive('hasAnyPermission')
            ->with('user:viewany')
            ->andReturn(true);

        $policy = new UserPolicy;
        $this->assertTrue($policy->view($user, $model));
    }

    public function test_view_returns_true_when_user_views_own_profile(): void
    {
        $user = Mockery::mock(User::class);
        $model = Mockery::mock(User::class);
        // Eloquent magic: access id via getAttribute
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $model->shouldReceive('getAttribute')->with('id')->andReturn(1);

        $user->shouldReceive('hasAnyPermission')
            ->with('user:viewany')
            ->andReturn(false);
        $user->shouldReceive('hasAnyPermission')
            ->with('user:view')
            ->andReturn(true);

        $policy = new UserPolicy;
        $this->assertTrue($policy->view($user, $model));
    }

    public function test_create_returns_true_when_user_has_permission(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasAnyPermission')
            ->once()
            ->with('user:create')
            ->andReturn(true);

        $policy = new UserPolicy;
        $this->assertTrue($policy->create($user));
    }

    public function test_update_returns_true_when_user_has_update_any_permission(): void
    {
        $user = Mockery::mock(User::class);
        $model = Mockery::mock(User::class);
        $user->shouldReceive('hasAnyPermission')
            ->with('user:updateany')
            ->andReturn(true);

        $policy = new UserPolicy;
        $this->assertTrue($policy->update($user, $model));
    }

    public function test_delete_any_returns_true_when_user_has_permission(): void
    {
        $user = Mockery::mock(User::class);
        $model = Mockery::mock(User::class);
        $user->shouldReceive('hasAnyPermission')
            ->once()
            ->with('user:deleteany')
            ->andReturn(true);

        $policy = new UserPolicy;
        $this->assertTrue($policy->deleteAny($user, $model));
    }
}
