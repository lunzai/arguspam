<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\AuthController;
use App\Http\Resources\User\MeResource;
use App\Models\User;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_me_returns_me_resource_with_loaded_relationships(): void
    {
        $mockUser = Mockery::mock(User::class);
        $mockUser->shouldReceive('loadMissing')->once()->with(
            'orgs',
            'userGroups',
            'restrictions',
            'roles',
        )->andReturnSelf();
        $mockUser->shouldReceive('loadCount')->once()->with('scheduledSessions', 'submittedRequests')->andReturnSelf();

        $mockRequest = Mockery::mock(Request::class);
        $mockRequest->shouldReceive('user')->once()->andReturn($mockUser);

        $controller = new AuthController();
        $result = $controller->me($mockRequest);

        $this->assertInstanceOf(MeResource::class, $result);
    }
}


