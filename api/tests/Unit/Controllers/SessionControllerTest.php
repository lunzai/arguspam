<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\SessionController;
use App\Services\Jit\Secrets\SecretsManager;
use Mockery;
use Tests\TestCase;

/**
 * @runInSeparateProcess
 */
class SessionControllerTest extends TestCase
{
    // Minimal tests to avoid alias conflicts from static Eloquent calls
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_controller_has_index_and_show_methods(): void
    {
        $secretsManager = \Mockery::mock(SecretsManager::class);
        $controller = new SessionController($secretsManager);
        $this->assertTrue(method_exists($controller, 'index'));
        $this->assertTrue(method_exists($controller, 'show'));
    }

    // Further unit tests require refactoring to inject dependencies
}
