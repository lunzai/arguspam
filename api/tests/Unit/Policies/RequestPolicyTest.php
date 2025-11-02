<?php

namespace Tests\Unit\Policies;

use App\Models\Request as RequestModel;
use App\Models\User;
use App\Policies\RequestPolicy;
use Mockery;
use Tests\TestCase;

class RequestPolicyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_view_returns_true_when_user_has_permission(): void
    {
        $user = Mockery::mock(User::class);
        $request = Mockery::mock(RequestModel::class);
        $user->shouldReceive('hasAnyPermission')
            ->once()
            ->with('request:view')
            ->andReturn(true);

        $policy = new RequestPolicy;
        $this->assertTrue($policy->view($user, $request));
    }

    public function test_create_returns_true_when_user_has_permission(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasAnyPermission')
            ->once()
            ->with('request:create')
            ->andReturn(true);

        $policy = new RequestPolicy;
        $this->assertTrue($policy->create($user));
    }

    public function test_approve_any_returns_true_when_user_has_permission(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasPermissionTo')
            ->once()
            ->with('request:approveany')
            ->andReturn(true);

        $policy = new RequestPolicy;
        $this->assertTrue($policy->approveAny($user));
    }

    public function test_approve_returns_true_when_user_has_approve_any_permission(): void
    {
        $user = Mockery::mock(User::class);
        $request = Mockery::mock(RequestModel::class);
        $asset = Mockery::mock();
        $request->shouldReceive('getAttribute')->with('asset')->andReturn($asset);

        $user->shouldReceive('hasPermissionTo')
            ->with('request:approveany')
            ->andReturn(true);

        $policy = new RequestPolicy;
        $this->assertTrue($policy->approve($user, $request));
    }

    public function test_cancel_returns_true_when_requester_is_user(): void
    {
        $user = Mockery::mock(User::class);
        $request = Mockery::mock(RequestModel::class);
        $requester = Mockery::mock(User::class);
        $request->shouldReceive('getAttribute')->with('requester')->andReturn($requester);

        $user->shouldReceive('hasPermissionTo')
            ->with('request:cancelany')
            ->andReturn(false);
        $user->shouldReceive('hasPermissionTo')
            ->with('request:cancel')
            ->andReturn(true);
        $requester->shouldReceive('is')
            ->with($user)
            ->andReturn(true);

        $policy = new RequestPolicy;
        $this->assertTrue($policy->cancel($user, $request));
    }
}
