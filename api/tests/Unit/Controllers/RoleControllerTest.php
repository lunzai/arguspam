<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\RoleController;
use Mockery;
use Tests\TestCase;

/**
 * @runInSeparateProcess
 */
class RoleControllerTest extends TestCase
{
    // Minimal tests to avoid alias conflicts from static Eloquent calls
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_controller_has_index_and_show_methods(): void
    {
        $controller = new RoleController;
        $this->assertTrue(method_exists($controller, 'index'));
        $this->assertTrue(method_exists($controller, 'show'));
    }

    // Further unit tests require refactoring to inject dependencies
}
