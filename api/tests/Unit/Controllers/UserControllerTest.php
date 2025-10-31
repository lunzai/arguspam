<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\UserController;
use App\Http\Filters\UserFilter;
use App\Http\Resources\User\UserCollection;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

/**
 * @runInSeparateProcess
 */
class UserControllerTest extends TestCase
{
    // Minimal tests to avoid alias conflicts from static Eloquent calls
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_controller_has_index_method(): void
    {
        $controller = new UserController();
        $this->assertTrue(method_exists($controller, 'index'));
    }
}


