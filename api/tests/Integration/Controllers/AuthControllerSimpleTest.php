<?php

namespace Tests\Integration\Controllers;

use App\Http\Controllers\AuthController;
use App\Http\Resources\User\MeResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AuthControllerSimpleTest extends TestCase
{
    use RefreshDatabase;

    private AuthController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = new AuthController;
    }

    public function test_me_returns_current_user_resource(): void
    {
        $user = User::factory()->create();
        $request = Request::create('/api/me');
        $request->setUserResolver(fn () => $user);

        $response = $this->controller->me($request);

        $this->assertInstanceOf(MeResource::class, $response);
        $this->assertEquals($user->id, $response->resource->id);
    }

    public function test_controller_extends_base_controller(): void
    {
        $this->assertInstanceOf(\App\Http\Controllers\Controller::class, $this->controller);
    }

    public function test_controller_has_required_methods(): void
    {
        $this->assertTrue(method_exists($this->controller, 'me'));
        $this->assertTrue(method_exists($this->controller, 'login'));
        $this->assertTrue(method_exists($this->controller, 'logout'));
    }

    public function test_me_method_returns_user_resource(): void
    {
        $user = User::factory()->create();
        $request = new Request;
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $result = $this->controller->me($request);

        $this->assertInstanceOf(MeResource::class, $result);
    }
}
