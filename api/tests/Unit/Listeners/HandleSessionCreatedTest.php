<?php

namespace Tests\Unit\Listeners;

use App\Events\SessionCreated;
use App\Listeners\HandleSessionCreated;
use App\Models\Asset;
use App\Models\Org;
use App\Models\Request as RequestModel;
use App\Models\Session;
use App\Models\User;
use App\Notifications\SessionCreatedNotifyApprover;
use App\Notifications\SessionCreatedNotifyRequester;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class HandleSessionCreatedTest extends TestCase
{
    private HandleSessionCreated $listener;
    private User $requester;
    private User $approver;
    private Org $org;
    private Asset $asset;
    private RequestModel $request;
    private Session $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->listener = new HandleSessionCreated;
        $this->requester = new User;
        $this->requester->id = 1;
        $this->approver = new User;
        $this->approver->id = 2;
        $this->session = \Mockery::mock(Session::class);
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
        $this->assertInstanceOf(HandleSessionCreated::class, $this->listener);
    }

    public function test_handle_method_exists(): void
    {
        $this->assertTrue(method_exists($this->listener, 'handle'));
    }

    public function test_handle_method_accepts_session_created_event(): void
    {
        $reflection = new \ReflectionClass(HandleSessionCreated::class);
        $handleMethod = $reflection->getMethod('handle');
        $parameters = $handleMethod->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('App\Events\SessionCreated', $parameters[0]->getType()->getName());
    }

    public function test_handle_method_returns_void(): void
    {
        $reflection = new \ReflectionClass(HandleSessionCreated::class);
        $handleMethod = $reflection->getMethod('handle');

        $this->assertEquals('void', $handleMethod->getReturnType()->getName());
    }

    public function test_handle_method_is_public(): void
    {
        $reflection = new \ReflectionClass(HandleSessionCreated::class);
        $handleMethod = $reflection->getMethod('handle');

        $this->assertTrue($handleMethod->isPublic());
    }

    public function test_constructor_is_empty(): void
    {
        $reflection = new \ReflectionClass(HandleSessionCreated::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertCount(0, $constructor->getParameters());
    }

    public function test_handle_sends_notification_to_requester(): void
    {
        Notification::fake();

        // Mock session relationships
        $this->session->shouldReceive('getAttribute')->with('requester')->andReturn($this->requester);
        // Make approver same as requester to avoid approver notification in this test
        $this->session->shouldReceive('getAttribute')->with('approver_id')->andReturn(1);
        $this->session->shouldReceive('getAttribute')->with('requester_id')->andReturn(1);
        $this->session->shouldReceive('setAttribute')->andReturnSelf();
        $this->session->approver_id = 1;
        $this->session->requester_id = 1;
        // notification captured by Notification::fake

        $event = new SessionCreated($this->session);
        $this->listener->handle($event);

        Notification::assertSentTo(
            $this->requester,
            SessionCreatedNotifyRequester::class
        );
    }

    public function test_handle_sends_notification_to_approver_when_different_from_requester(): void
    {
        Notification::fake();

        // Mock session relationships
        $this->session->shouldReceive('getAttribute')->with('requester')->andReturn($this->requester);
        $this->session->shouldReceive('getAttribute')->with('approver')->andReturn($this->approver);
        $this->session->shouldReceive('getAttribute')->with('approver_id')->andReturn(1);
        $this->session->shouldReceive('getAttribute')->with('requester_id')->andReturn(2);
        $this->session->shouldReceive('setAttribute')->andReturnSelf();
        $this->session->approver_id = 1;
        $this->session->requester_id = 2;
        // notifications captured by Notification::fake

        $event = new SessionCreated($this->session);
        $this->listener->handle($event);

        Notification::assertSentTo(
            $this->approver,
            SessionCreatedNotifyApprover::class
        );
    }

    public function test_handle_does_not_notify_approver_when_approver_is_same_as_requester(): void
    {
        Notification::fake();

        // Mock session relationships
        $this->session->shouldReceive('getAttribute')->with('requester')->andReturn($this->requester);
        $this->session->shouldReceive('getAttribute')->with('approver_id')->andReturn(1);
        $this->session->shouldReceive('getAttribute')->with('requester_id')->andReturn(1);
        $this->session->shouldReceive('setAttribute')->andReturnSelf();
        $this->session->approver_id = 1;
        $this->session->requester_id = 1; // Same as approver
        // notification captured by Notification::fake

        $event = new SessionCreated($this->session);
        $this->listener->handle($event);

        Notification::assertSentTo(
            $this->requester,
            SessionCreatedNotifyRequester::class
        );
        Notification::assertNotSentTo(
            $this->approver,
            SessionCreatedNotifyApprover::class
        );
    }

    public function test_handle_uses_correct_notification_classes(): void
    {
        $reflection = new \ReflectionClass(HandleSessionCreated::class);
        $filename = $reflection->getFileName();
        $content = file_get_contents($filename);

        $this->assertStringContainsString('SessionCreatedNotifyApprover', $content);
        $this->assertStringContainsString('SessionCreatedNotifyRequester', $content);
    }

    public function test_handle_method_implementation(): void
    {
        $reflection = new \ReflectionClass(HandleSessionCreated::class);
        $filename = $reflection->getFileName();
        $content = file_get_contents($filename);

        $this->assertStringContainsString('public function handle(SessionCreated $event): void', $content);
        $this->assertStringContainsString('use App\Events\SessionCreated;', $content);
        $this->assertStringContainsString('use Illuminate\Contracts\Queue\ShouldQueue;', $content);
        $this->assertStringContainsString('use Illuminate\Contracts\Queue\ShouldBeEncrypted;', $content);
        $this->assertStringContainsString('use Illuminate\Queue\InteractsWithQueue;', $content);
    }

    public function test_listener_uses_correct_namespace(): void
    {
        $this->assertStringStartsWith('App\Listeners', HandleSessionCreated::class);
    }

    public function test_listener_can_be_serialized(): void
    {
        $serialized = serialize($this->listener);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(HandleSessionCreated::class, $unserialized);
    }

    public function test_listener_has_correct_class_name(): void
    {
        $this->assertEquals('HandleSessionCreated', class_basename(HandleSessionCreated::class));
    }

    public function test_listener_file_exists(): void
    {
        $reflection = new \ReflectionClass(HandleSessionCreated::class);
        $filename = $reflection->getFileName();

        $this->assertFileExists($filename);
        $this->assertStringEndsWith('HandleSessionCreated.php', $filename);
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
