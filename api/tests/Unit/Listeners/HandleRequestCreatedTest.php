<?php

namespace Tests\Unit\Listeners;

use App\Events\RequestCreated;
use App\Listeners\HandleRequestCreated;
use App\Models\Request;
use App\Services\OpenAI\OpenAiService;
use Mockery;
use Tests\TestCase;

class HandleRequestCreatedTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_listener_implements_should_queue(): void
    {
        $listener = new HandleRequestCreated(Mockery::mock(OpenAiService::class));

        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $listener);
    }

    public function test_listener_implements_should_be_encrypted(): void
    {
        $listener = new HandleRequestCreated(Mockery::mock(OpenAiService::class));

        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldBeEncrypted::class, $listener);
    }

    public function test_listener_has_required_traits(): void
    {
        $listener = new HandleRequestCreated(Mockery::mock(OpenAiService::class));

        $this->assertTrue(in_array('Illuminate\Queue\InteractsWithQueue', class_uses($listener)));
    }

    public function test_listener_has_retry_properties(): void
    {
        $listener = new HandleRequestCreated(Mockery::mock(OpenAiService::class));

        $this->assertEquals(3, $listener->tries);
        $this->assertEquals(5, $listener->backoff);
    }

    public function test_constructor_injects_openai_service(): void
    {
        $openAiService = Mockery::mock(OpenAiService::class);
        $listener = new HandleRequestCreated($openAiService);

        $reflection = new \ReflectionClass($listener);
        $property = $reflection->getProperty('openAiService');
        $property->setAccessible(true);

        $this->assertSame($openAiService, $property->getValue($listener));
    }

    public function test_handle_method_exists(): void
    {
        $listener = new HandleRequestCreated(Mockery::mock(OpenAiService::class));

        $this->assertTrue(method_exists($listener, 'handle'));
    }

    public function test_handle_method_accepts_request_created_event(): void
    {
        $reflection = new \ReflectionClass(HandleRequestCreated::class);
        $handleMethod = $reflection->getMethod('handle');
        $parameters = $handleMethod->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('App\Events\RequestCreated', $parameters[0]->getType()->getName());
    }

    public function test_handle_method_returns_void(): void
    {
        $reflection = new \ReflectionClass(HandleRequestCreated::class);
        $handleMethod = $reflection->getMethod('handle');

        $this->assertEquals('void', $handleMethod->getReturnType()->getName());
    }

    public function test_handle_method_calls_request_methods(): void
    {
        $openAiService = Mockery::mock(OpenAiService::class);
        $request = Mockery::mock(Request::class);

        $request->shouldReceive('getAiEvaluation')
            ->with($openAiService)
            ->once();

        $request->shouldReceive('submit')
            ->once();

        $event = Mockery::mock(RequestCreated::class);
        $event->request = $request;

        $listener = new HandleRequestCreated($openAiService);
        $listener->handle($event);
        $this->assertTrue(true);
    }

    public function test_failed_method_exists(): void
    {
        $listener = new HandleRequestCreated(Mockery::mock(OpenAiService::class));

        $this->assertTrue(method_exists($listener, 'failed'));
    }

    public function test_failed_method_accepts_correct_parameters(): void
    {
        $reflection = new \ReflectionClass(HandleRequestCreated::class);
        $failedMethod = $reflection->getMethod('failed');
        $parameters = $failedMethod->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('App\Events\RequestCreated', $parameters[0]->getType()->getName());
        $this->assertEquals('Throwable', $parameters[1]->getType()->getName());
    }

    public function test_failed_method_returns_void(): void
    {
        $reflection = new \ReflectionClass(HandleRequestCreated::class);
        $failedMethod = $reflection->getMethod('failed');

        $this->assertEquals('void', $failedMethod->getReturnType()->getName());
    }

    public function test_listener_can_be_instantiated_with_openai_service(): void
    {
        $openAiService = Mockery::mock(OpenAiService::class);
        $listener = new HandleRequestCreated($openAiService);

        $this->assertInstanceOf(HandleRequestCreated::class, $listener);
    }

    public function test_listener_has_correct_property_visibility(): void
    {
        $listener = new HandleRequestCreated(Mockery::mock(OpenAiService::class));

        $reflection = new \ReflectionClass($listener);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        $propertyNames = array_map(fn ($prop) => $prop->getName(), $properties);
        $this->assertContains('tries', $propertyNames);
        $this->assertContains('backoff', $propertyNames);
    }

    public function test_listener_uses_correct_namespace(): void
    {
        $this->assertStringStartsWith('App\Listeners', HandleRequestCreated::class);
    }

    public function test_listener_file_exists(): void
    {
        $reflection = new \ReflectionClass(HandleRequestCreated::class);
        $filename = $reflection->getFileName();

        $this->assertFileExists($filename);
        $this->assertStringEndsWith('HandleRequestCreated.php', $filename);
    }
}
