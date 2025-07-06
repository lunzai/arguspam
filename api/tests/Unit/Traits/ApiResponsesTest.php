<?php

namespace Tests\Unit\Traits;

use App\Traits\ApiResponses;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class ApiResponsesTest extends TestCase
{
    private TestApiResponsesController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new TestApiResponsesController();
    }

    public function test_ok_returns_no_content_response_with_200_status(): void
    {
        $response = $this->controller->ok();
        
        $this->assertInstanceOf(\Illuminate\Http\Response::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    public function test_created_returns_no_content_response_with_201_status(): void
    {
        $response = $this->controller->created();
        
        $this->assertInstanceOf(\Illuminate\Http\Response::class, $response);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    public function test_accepted_returns_no_content_response_with_202_status(): void
    {
        $response = $this->controller->accepted();
        
        $this->assertInstanceOf(\Illuminate\Http\Response::class, $response);
        $this->assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    public function test_no_content_returns_no_content_response_with_204_status(): void
    {
        $response = $this->controller->noContent();
        
        $this->assertInstanceOf(\Illuminate\Http\Response::class, $response);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    public function test_unprocessable_entity_aborts_with_422_status_and_message(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Validation failed');
        
        $this->controller->unprocessableEntity('Validation failed');
    }

    public function test_unauthorized_aborts_with_401_status_and_message(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Not authenticated');
        
        $this->controller->unauthorized('Not authenticated');
    }

    public function test_forbidden_aborts_with_403_status_and_message(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Access denied');
        
        $this->controller->forbidden('Access denied');
    }

    public function test_success_returns_json_response_with_data_and_default_200_status(): void
    {
        $data = ['id' => 1, 'name' => 'Test'];
        $response = $this->controller->success($data);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        $responseData = $response->getData(true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals($data, $responseData['data']);
    }

    public function test_success_returns_json_response_with_empty_data_by_default(): void
    {
        $response = $this->controller->success();
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        $responseData = $response->getData(true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals([], $responseData['data']);
    }

    public function test_success_returns_json_response_with_custom_status_code(): void
    {
        $data = ['message' => 'Custom success'];
        $response = $this->controller->success($data, Response::HTTP_CREATED);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        
        $responseData = $response->getData(true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals($data, $responseData['data']);
    }

    public function test_success_handles_complex_data_structures(): void
    {
        $data = [
            'users' => [
                ['id' => 1, 'name' => 'John'],
                ['id' => 2, 'name' => 'Jane']
            ],
            'meta' => [
                'total' => 2,
                'page' => 1
            ]
        ];
        $response = $this->controller->success($data);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        $responseData = $response->getData(true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals($data, $responseData['data']);
    }

    public function test_success_handles_null_data(): void
    {
        $response = $this->controller->success(null);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        $responseData = $response->getData(true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertNull($responseData['data']);
    }

    public function test_success_handles_string_data(): void
    {
        $data = 'Simple string response';
        $response = $this->controller->success($data);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        $responseData = $response->getData(true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals($data, $responseData['data']);
    }

    public function test_success_handles_boolean_data(): void
    {
        $response = $this->controller->success(true);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        $responseData = $response->getData(true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertTrue($responseData['data']);
    }

    public function test_success_handles_numeric_data(): void
    {
        $response = $this->controller->success(42);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        
        $responseData = $response->getData(true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals(42, $responseData['data']);
    }

    public function test_all_abort_methods_throw_http_exception(): void
    {
        $methods = [
            ['unprocessableEntity', 'Test message'],
            ['unauthorized', 'Auth failed'],
            ['forbidden', 'Access denied']
        ];

        foreach ($methods as [$method, $message]) {
            try {
                $this->controller->$method($message);
                $this->fail("Expected HttpException for method $method");
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
                $this->assertEquals($message, $e->getMessage());
            }
        }
    }

    public function test_response_methods_return_correct_types(): void
    {
        $this->assertInstanceOf(\Illuminate\Http\Response::class, $this->controller->ok());
        $this->assertInstanceOf(\Illuminate\Http\Response::class, $this->controller->created());
        $this->assertInstanceOf(\Illuminate\Http\Response::class, $this->controller->accepted());
        $this->assertInstanceOf(\Illuminate\Http\Response::class, $this->controller->noContent());
        $this->assertInstanceOf(JsonResponse::class, $this->controller->success());
    }
}

// Test controller using the ApiResponses trait
class TestApiResponsesController
{
    use ApiResponses {
        ok as public;
        created as public;
        accepted as public;
        noContent as public;
        unprocessableEntity as public;
        unauthorized as public;
        forbidden as public;
        success as public;
    }
}