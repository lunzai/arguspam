<?php

namespace Tests\Unit\Listeners;

use App\Listeners\HandleSessionAiReviewed;
use Tests\TestCase;

class HandleSessionAiReviewedSimpleTest extends TestCase
{
    private HandleSessionAiReviewed $listener;

    protected function setUp(): void
    {
        parent::setUp();
        $this->listener = new HandleSessionAiReviewed;
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

    public function test_listener_can_be_instantiated(): void
    {
        $this->assertInstanceOf(HandleSessionAiReviewed::class, $this->listener);
    }

    public function test_handle_method_exists(): void
    {
        $this->assertTrue(method_exists($this->listener, 'handle'));
    }

    public function test_handle_method_accepts_session_ai_audited_event(): void
    {
        $reflection = new \ReflectionClass(HandleSessionAiReviewed::class);
        $handleMethod = $reflection->getMethod('handle');
        $parameters = $handleMethod->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('App\Events\SessionAiAudited', $parameters[0]->getType()->getName());
    }

    public function test_handle_method_returns_void(): void
    {
        $reflection = new \ReflectionClass(HandleSessionAiReviewed::class);
        $handleMethod = $reflection->getMethod('handle');

        $this->assertEquals('void', $handleMethod->getReturnType()->getName());
    }

    public function test_handle_method_is_public(): void
    {
        $reflection = new \ReflectionClass(HandleSessionAiReviewed::class);
        $handleMethod = $reflection->getMethod('handle');

        $this->assertTrue($handleMethod->isPublic());
    }

    public function test_constructor_is_empty(): void
    {
        $reflection = new \ReflectionClass(HandleSessionAiReviewed::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertCount(0, $constructor->getParameters());
    }

    public function test_handle_uses_correct_notification_classes(): void
    {
        $reflection = new \ReflectionClass(HandleSessionAiReviewed::class);
        $filename = $reflection->getFileName();
        $content = file_get_contents($filename);

        $this->assertStringContainsString('SessionReviewOptionalNotifyApprover', $content);
        $this->assertStringContainsString('SessionReviewOptionalNotifyRequester', $content);
        $this->assertStringContainsString('SessionReviewRequiredNotifyApprover', $content);
        $this->assertStringContainsString('SessionReviewRequiredNotifyRequester', $content);
    }

    public function test_handle_method_implementation(): void
    {
        $reflection = new \ReflectionClass(HandleSessionAiReviewed::class);
        $filename = $reflection->getFileName();
        $content = file_get_contents($filename);

        $this->assertStringContainsString('public function handle(SessionAiAudited $event): void', $content);
        $this->assertStringContainsString('use App\Events\SessionAiAudited;', $content);
        $this->assertStringContainsString('use Illuminate\Contracts\Queue\ShouldQueue;', $content);
        $this->assertStringContainsString('use Illuminate\Contracts\Queue\ShouldBeEncrypted;', $content);
        $this->assertStringContainsString('use Illuminate\Queue\InteractsWithQueue;', $content);
    }

    public function test_listener_uses_correct_namespace(): void
    {
        $this->assertStringStartsWith('App\Listeners', HandleSessionAiReviewed::class);
    }

    public function test_listener_can_be_serialized(): void
    {
        $serialized = serialize($this->listener);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(HandleSessionAiReviewed::class, $unserialized);
    }

    public function test_listener_has_correct_class_name(): void
    {
        $this->assertEquals('HandleSessionAiReviewed', class_basename(HandleSessionAiReviewed::class));
    }

    public function test_listener_file_exists(): void
    {
        $reflection = new \ReflectionClass(HandleSessionAiReviewed::class);
        $filename = $reflection->getFileName();

        $this->assertFileExists($filename);
        $this->assertStringEndsWith('HandleSessionAiReviewed.php', $filename);
    }
}
