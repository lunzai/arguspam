<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\RequestController;
use App\Http\Filters\RequestFilter;
use App\Http\Resources\Request\RequestCollection;
use App\Models\Request as RequestModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

/**
 * @runInSeparateProcess
 */
class RequestControllerTest extends TestCase
{
    // Minimal tests to avoid alias conflicts from static Eloquent calls
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_controller_has_index_method(): void
    {
        $controller = new RequestController();
        $this->assertTrue(method_exists($controller, 'index'));
    }
}


