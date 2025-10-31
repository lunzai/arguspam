<?php

namespace Tests\Unit\Policies;

use App\Models\Session;
use App\Models\User;
use App\Policies\SessionPolicy;
use Mockery;
use Tests\TestCase;

class SessionPolicyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_view_returns_true_when_user_has_permission(): void
    {
        $user = Mockery::mock(User::class);
        $session = Mockery::mock(Session::class);
        $user->shouldReceive('hasPermissionTo')
            ->once()
            ->with('session:view')
            ->andReturn(true);

        $policy = new SessionPolicy();
        $this->assertTrue($policy->view($user, $session));
    }

    public function test_terminate_returns_true_when_user_has_terminate_any_permission(): void
    {
        $user = Mockery::mock(User::class);
        $session = Mockery::mock(Session::class);
        $user->shouldReceive('hasPermissionTo')
            ->with('session:terminateany')
            ->andReturn(true);

        $policy = new SessionPolicy();
        $this->assertTrue($policy->terminateAny($user));
    }

    public function test_retrieve_secret_returns_true_when_user_has_permission_and_is_requester(): void
    {
        $user = Mockery::mock(User::class);
        $session = Mockery::mock(Session::class);
        $requester = Mockery::mock(User::class);
        $session->shouldReceive('getAttribute')->with('requester')->andReturn($requester);
        
        $user->shouldReceive('hasPermissionTo')
            ->once()
            ->with('session:retrievesecret')
            ->andReturn(true);
        $requester->shouldReceive('is')
            ->once()
            ->with($user)
            ->andReturn(true);

        $policy = new SessionPolicy();
        $this->assertTrue($policy->retrieveSecret($user, $session));
    }

    public function test_start_returns_true_when_user_has_permission_and_is_requester(): void
    {
        $user = Mockery::mock(User::class);
        $session = Mockery::mock(Session::class);
        $requester = Mockery::mock(User::class);
        $session->shouldReceive('getAttribute')->with('requester')->andReturn($requester);
        
        $user->shouldReceive('hasPermissionTo')
            ->once()
            ->with('session:start')
            ->andReturn(true);
        $requester->shouldReceive('is')
            ->once()
            ->with($user)
            ->andReturn(true);

        $policy = new SessionPolicy();
        $this->assertTrue($policy->start($user, $session));
    }
}

