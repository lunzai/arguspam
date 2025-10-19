<?php

namespace Tests\Unit\Listeners;

use App\Events\RequestCancelled;
use App\Listeners\HandleRequestCancelled;
use Tests\TestCase;

class HandleRequestCancelledSimpleTest extends TestCase
{
    private HandleRequestCancelled $listener;

    protected function setUp(): void
    {
        parent::setUp();
        $this->listener = new HandleRequestCancelled();
    }

    public function test_listener_implements_should_queue(): void
    {
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $this->listener);
    }

    public function test_listener_implements_should_be_encrypted(): void
    {
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldBeEncrypted::class, $this->listener);
    }

    public function test_listener_has_required_traits(): void
    {
        $this->assertTrue(in_array('Illuminate\Queue\InteractsWithQueue', class_uses($this->listener)));
    }

    public function test_listener_has_retry_properties(): void
    {
        $this->assertEquals(3, $this->listener->tries);
        $this->assertEquals(5, $this->listener->backoff);
    }

    public function test_listener_can_be_instantiated(): void
    {
        $this->assertInstanceOf(HandleRequestCancelled::class, $this->listener);
    }

    public function test_handle_method_exists(): void
    {
        $this->assertTrue(method_exists($this->listener, 'handle'));
    }

    public function test_handle_method_accepts_request_cancelled_event(): void
    {
        $reflection = new \ReflectionClass(HandleRequestCancelled::class);
        $handleMethod = $reflection->getMethod('handle');
        $parameters = $handleMethod->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('App\Events\RequestCancelled', $parameters[0]->getType()->getName());
    }

    public function test_handle_method_returns_void(): void
    {
        $reflection = new \ReflectionClass(HandleRequestCancelled::class);
        $handleMethod = $reflection->getMethod('handle');

        $this->assertEquals('void', $handleMethod->getReturnType()->getName());
    }

    public function test_handle_method_is_public(): void
    {
        $reflection = new \ReflectionClass(HandleRequestCancelled::class);
        $handleMethod = $reflection->getMethod('handle');

        $this->assertTrue($handleMethod->isPublic());
    }

    public function test_failed_method_exists(): void
    {
        $this->assertTrue(method_exists($this->listener, 'failed'));
    }

    public function test_failed_method_accepts_correct_parameters(): void
    {
        $reflection = new \ReflectionClass(HandleRequestCancelled::class);
        $failedMethod = $reflection->getMethod('failed');
        $parameters = $failedMethod->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('App\Events\RequestCancelled', $parameters[0]->getType()->getName());
        $this->assertEquals('Throwable', $parameters[1]->getType()->getName());
    }

    public function test_failed_method_returns_void(): void
    {
        $reflection = new \ReflectionClass(HandleRequestCancelled::class);
        $failedMethod = $reflection->getMethod('failed');

        $this->assertEquals('void', $failedMethod->getReturnType()->getName());
    }

    public function test_constructor_is_empty(): void
    {
        $reflection = new \ReflectionClass(HandleRequestCancelled::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertCount(0, $constructor->getParameters());
    }

    public function test_handle_uses_correct_notification_classes(): void
    {
        $reflection = new \ReflectionClass(HandleRequestCancelled::class);
        $filename = $reflection->getFileName();
        $content = file_get_contents($filename);

        $this->assertStringContainsString('RequestCancelledNotifyApprover', $content);
        $this->assertStringContainsString('RequestCancelledNotifyRequester', $content);
    }

    public function test_handle_method_implementation(): void
    {
        $reflection = new \ReflectionClass(HandleRequestCancelled::class);
        $filename = $reflection->getFileName();
        $content = file_get_contents($filename);

        $this->assertStringContainsString('public function handle(RequestCancelled $event): void', $content);
        $this->assertStringContainsString('use App\Events\RequestCancelled;', $content);
        $this->assertStringContainsString('use Illuminate\Contracts\Queue\ShouldQueue;', $content);
        $this->assertStringContainsString('use Illuminate\Contracts\Queue\ShouldBeEncrypted;', $content);
        $this->assertStringContainsString('use Illuminate\Queue\InteractsWithQueue;', $content);
        $this->assertStringContainsString('Notification::send', $content);
    }

    public function test_failed_method_implementation(): void
    {
        $reflection = new \ReflectionClass(HandleRequestCancelled::class);
        $filename = $reflection->getFileName();
        $content = file_get_contents($filename);

        $this->assertStringContainsString('public function failed(RequestCancelled $event, \\Throwable $exception): void', $content);
        $this->assertStringContainsString('\\Log::error', $content);
    }

    public function test_listener_uses_correct_namespace(): void
    {
        $this->assertStringStartsWith('App\Listeners', HandleRequestCancelled::class);
    }

    public function test_listener_can_be_serialized(): void
    {
        $serialized = serialize($this->listener);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(HandleRequestCancelled::class, $unserialized);
    }

    public function test_listener_has_correct_class_name(): void
    {
        $this->assertEquals('HandleRequestCancelled', class_basename(HandleRequestCancelled::class));
    }

    public function test_listener_file_exists(): void
    {
        $reflection = new \ReflectionClass(HandleRequestCancelled::class);
        $filename = $reflection->getFileName();

        $this->assertFileExists($filename);
        $this->assertStringEndsWith('HandleRequestCancelled.php', $filename);
    }

    public function test_listener_has_correct_property_visibility(): void
    {
        $reflection = new \ReflectionClass($this->listener);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);

        $propertyNames = array_map(fn ($prop) => $prop->getName(), $properties);
        $this->assertContains('tries', $propertyNames);
        $this->assertContains('backoff', $propertyNames);
    }
}
