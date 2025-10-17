<?php

namespace Tests\Unit\Listeners;

use App\Listeners\HandleSessionStarted;
use Tests\TestCase;

class HandleSessionStartedTest extends TestCase
{
    public function test_listener_implements_should_queue(): void
    {
        $listener = new HandleSessionStarted;

        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $listener);
    }

    public function test_listener_has_required_traits(): void
    {
        $listener = new HandleSessionStarted;

        $this->assertTrue(in_array('Illuminate\Queue\InteractsWithQueue', class_uses($listener)));
    }

    public function test_listener_can_be_instantiated(): void
    {
        $listener = new HandleSessionStarted;

        $this->assertInstanceOf(HandleSessionStarted::class, $listener);
    }

    public function test_handle_method_exists(): void
    {
        $listener = new HandleSessionStarted;

        $this->assertTrue(method_exists($listener, 'handle'));
    }

    public function test_handle_method_accepts_session_started_event(): void
    {
        $reflection = new \ReflectionClass(HandleSessionStarted::class);
        $handleMethod = $reflection->getMethod('handle');
        $parameters = $handleMethod->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('App\Events\SessionStarted', $parameters[0]->getType()->getName());
    }

    public function test_handle_method_returns_void(): void
    {
        $reflection = new \ReflectionClass(HandleSessionStarted::class);
        $handleMethod = $reflection->getMethod('handle');

        $this->assertEquals('void', $handleMethod->getReturnType()->getName());
    }

    public function test_handle_method_is_public(): void
    {
        $reflection = new \ReflectionClass(HandleSessionStarted::class);
        $handleMethod = $reflection->getMethod('handle');

        $this->assertTrue($handleMethod->isPublic());
    }

    public function test_constructor_is_empty(): void
    {
        $reflection = new \ReflectionClass(HandleSessionStarted::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertCount(0, $constructor->getParameters());
    }

    public function test_listener_uses_correct_namespace(): void
    {
        $this->assertStringStartsWith('App\Listeners', HandleSessionStarted::class);
    }

    public function test_listener_can_be_serialized(): void
    {
        $listener = new HandleSessionStarted;

        $serialized = serialize($listener);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(HandleSessionStarted::class, $unserialized);
    }

    public function test_listener_has_correct_class_name(): void
    {
        $this->assertEquals('HandleSessionStarted', class_basename(HandleSessionStarted::class));
    }

    public function test_listener_file_exists(): void
    {
        $reflection = new \ReflectionClass(HandleSessionStarted::class);
        $filename = $reflection->getFileName();

        $this->assertFileExists($filename);
        $this->assertStringEndsWith('HandleSessionStarted.php', $filename);
    }

    public function test_listener_has_handle_method_implementation(): void
    {
        $reflection = new \ReflectionClass(HandleSessionStarted::class);
        $filename = $reflection->getFileName();

        $content = file_get_contents($filename);

        // Check for key implementation details
        $this->assertStringContainsString('public function handle(SessionStarted $event): void', $content);
        $this->assertStringContainsString('use App\Events\SessionStarted;', $content);
        $this->assertStringContainsString('use Illuminate\Contracts\Queue\ShouldQueue;', $content);
        $this->assertStringContainsString('use Illuminate\Queue\InteractsWithQueue;', $content);
    }
}
