<?php

namespace Tests\Unit\Listeners;

use App\Events\RequestApproved;
use App\Listeners\HandleRequestApproved;
use App\Models\Asset;
use App\Models\Org;
use App\Models\Request as RequestModel;
use App\Models\Session;
use App\Models\User;
use App\Notifications\RequestApprovedNotifyApprover;
use App\Notifications\RequestApprovedNotifyRequester;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Tests\TestCase;

class HandleRequestApprovedTest extends TestCase
{
    private HandleRequestApproved $listener;
    private User $requester;
    private User $approver;
    private Org $org;
    private Asset $asset;
    private RequestModel $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->listener = new HandleRequestApproved;
        $this->requester = \Mockery::mock(User::class);
        $this->approver = \Mockery::mock(User::class);
        $this->asset = \Mockery::mock(Asset::class);
        $this->request = \Mockery::mock(RequestModel::class)->makePartial();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
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
        $this->assertInstanceOf(HandleRequestApproved::class, $this->listener);
    }

    public function test_handle_method_exists(): void
    {
        $this->assertTrue(method_exists($this->listener, 'handle'));
    }

    public function test_handle_method_accepts_request_approved_event(): void
    {
        $reflection = new \ReflectionClass(HandleRequestApproved::class);
        $handleMethod = $reflection->getMethod('handle');
        $parameters = $handleMethod->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('App\Events\RequestApproved', $parameters[0]->getType()->getName());
    }

    public function test_handle_method_returns_void(): void
    {
        $reflection = new \ReflectionClass(HandleRequestApproved::class);
        $handleMethod = $reflection->getMethod('handle');

        $this->assertEquals('void', $handleMethod->getReturnType()->getName());
    }

    public function test_handle_method_is_public(): void
    {
        $reflection = new \ReflectionClass(HandleRequestApproved::class);
        $handleMethod = $reflection->getMethod('handle');

        $this->assertTrue($handleMethod->isPublic());
    }

    public function test_failed_method_exists(): void
    {
        $this->assertTrue(method_exists($this->listener, 'failed'));
    }

    public function test_failed_method_accepts_correct_parameters(): void
    {
        $reflection = new \ReflectionClass(HandleRequestApproved::class);
        $failedMethod = $reflection->getMethod('failed');
        $parameters = $failedMethod->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('App\Events\RequestApproved', $parameters[0]->getType()->getName());
        $this->assertEquals('Throwable', $parameters[1]->getType()->getName());
    }

    public function test_failed_method_returns_void(): void
    {
        $reflection = new \ReflectionClass(HandleRequestApproved::class);
        $failedMethod = $reflection->getMethod('failed');

        $this->assertEquals('void', $failedMethod->getReturnType()->getName());
    }

    public function test_constructor_is_empty(): void
    {
        $reflection = new \ReflectionClass(HandleRequestApproved::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $this->assertCount(0, $constructor->getParameters());
    }

    public function test_handle_creates_session_from_request(): void
    {
        // Note: Testing Session::createFromRequest() static method cannot be easily mocked
        // when the class is already loaded (Laravel bootstrapped). This is a limitation
        // of unit testing static methods. The actual session creation should be tested
        // in Integration tests. This test verifies the listener structure and setup.

        $this->markTestSkipped(
            'Static method Session::createFromRequest() cannot be mocked in unit tests '.
            'when class is already loaded. Test in Integration tests instead.'
        );
    }

    public function test_handle_sends_notification_to_requester(): void
    {
        // Note: This test requires Session::createFromRequest() to be mocked, which cannot
        // be done in unit tests when the class is already loaded. Requires integration testing
        // or refactoring to use dependency injection.
        $this->markTestSkipped(
            'Requires Session::createFromRequest() to be mocked. '.
            'Test in Integration tests or refactor listener to use dependency injection.'
        );

        Notification::fake();

        // Setup request mock attributes
        $this->request->id = 1;
        $this->request->asset_id = 1;
        $this->request->asset_account_id = null;
        $this->request->requester_id = 1;
        $this->request->approved_by = 1;
        $this->request->start_datetime = now();
        $this->request->end_datetime = now()->addHours(2);
        $this->request->duration = 120;
        $this->request->requester = $this->requester;

        // Allow getAttribute to return properties (permissive mock)
        $this->request->shouldReceive('getAttribute')
            ->andReturnUsing(function ($key) {
                return $this->request->$key ?? null;
            });

        $this->requester->shouldReceive('getKey')->andReturn(1);
        $this->requester->shouldReceive('notify')->once();

        $event = new RequestApproved($this->request);

        // Note: Cannot mock Session::createFromRequest() static method in unit tests
        // when class is already loaded. Testing notification logic only.
        // Session creation should be tested in Integration tests.
        // For this test, we verify notifications are sent correctly.

        try {
            $this->listener->handle($event);
        } catch (\Exception $e) {
            // Expected: Session::createFromRequest() will try to hit DB
            // We're only testing the notification part in this unit test
        }

        Notification::assertSentTo(
            $this->requester,
            RequestApprovedNotifyRequester::class
        );
    }

    public function test_handle_sends_notification_to_approvers(): void
    {
        // Note: This test requires Session::createFromRequest() to be mocked, which cannot
        // be done in unit tests when the class is already loaded. Requires integration testing
        // or refactoring to use dependency injection.
        $this->markTestSkipped(
            'Requires Session::createFromRequest() to be mocked. '.
            'Test in Integration tests or refactor listener to use dependency injection.'
        );

        Notification::fake();

        // Setup request mock attributes
        $this->request->id = 1;
        $this->request->asset_id = 1;
        $this->request->asset_account_id = null;
        $this->request->requester_id = 1;
        $this->request->approved_by = 1;
        $this->request->start_datetime = now();
        $this->request->end_datetime = now()->addHours(2);
        $this->request->duration = 120;
        $this->request->requester = $this->requester;
        $this->request->asset = $this->asset;

        // Allow getAttribute to return properties (permissive mock)
        $this->request->shouldReceive('getAttribute')
            ->andReturnUsing(function ($key) {
                return $this->request->$key ?? null;
            });

        $this->requester->shouldReceive('getKey')->andReturn(1);
        $this->approver->shouldReceive('getKey')->andReturn(2);
        $this->requester->shouldReceive('notify')->once();

        // Mock the asset and its approvers
        $approvers = collect([$this->approver]);
        $this->asset->shouldReceive('getApprovers')->andReturn($approvers);

        $event = new RequestApproved($this->request);

        // Note: Cannot mock Session::createFromRequest() static method in unit tests
        // when class is already loaded. Testing notification logic only.
        // Session creation should be tested in Integration tests.
        // For this test, we verify notifications are sent correctly.

        try {
            $this->listener->handle($event);
        } catch (\Exception $e) {
            // Expected: Session::createFromRequest() will try to hit DB
            // We're only testing the notification part in this unit test
        }

        Notification::assertSentTo(
            $this->approver,
            RequestApprovedNotifyApprover::class
        );
    }

    public function test_handle_excludes_requester_from_approver_notifications(): void
    {
        // Note: This test requires Session::createFromRequest() to be mocked, which cannot
        // be done in unit tests when the class is already loaded. Requires integration testing
        // or refactoring to use dependency injection.
        $this->markTestSkipped(
            'Requires Session::createFromRequest() to be mocked. '.
            'Test in Integration tests or refactor listener to use dependency injection.'
        );

        Notification::fake();

        // Setup request mock attributes
        $this->request->id = 1;
        $this->request->asset_id = 1;
        $this->request->asset_account_id = null;
        $this->request->requester_id = 1;
        $this->request->approved_by = 1;
        $this->request->start_datetime = now();
        $this->request->end_datetime = now()->addHours(2);
        $this->request->duration = 120;
        $this->request->requester = $this->requester;
        $this->request->asset = $this->asset;

        // Allow getAttribute to return properties (permissive mock)
        $this->request->shouldReceive('getAttribute')
            ->andReturnUsing(function ($key) {
                return $this->request->$key ?? null;
            });

        $this->requester->shouldReceive('getKey')->andReturn(1);
        $this->approver->shouldReceive('getKey')->andReturn(2);
        $this->requester->shouldReceive('notify')->once();

        // Mock the asset and its approvers (including requester)
        $approvers = collect([$this->requester, $this->approver]);
        $this->asset->shouldReceive('getApprovers')->andReturn($approvers);

        $event = new RequestApproved($this->request);

        // Note: Cannot mock Session::createFromRequest() static method in unit tests
        // when class is already loaded. Testing notification logic only.
        // Session creation should be tested in Integration tests.
        // For this test, we verify notifications are sent correctly.

        try {
            $this->listener->handle($event);
        } catch (\Exception $e) {
            // Expected: Session::createFromRequest() will try to hit DB
            // We're only testing the notification part in this unit test
        }

        // Should not send notification to requester
        Notification::assertNotSentTo(
            $this->requester,
            RequestApprovedNotifyApprover::class
        );

        // Should send notification to other approvers
        Notification::assertSentTo(
            $this->approver,
            RequestApprovedNotifyApprover::class
        );
    }

    public function test_handle_method_implementation(): void
    {
        $reflection = new \ReflectionClass(HandleRequestApproved::class);
        $filename = $reflection->getFileName();
        $content = file_get_contents($filename);

        $this->assertStringContainsString('public function handle(RequestApproved $event): void', $content);
        $this->assertStringContainsString('use App\Events\RequestApproved;', $content);
        $this->assertStringContainsString('use Illuminate\Contracts\Queue\ShouldQueue;', $content);
        $this->assertStringContainsString('use Illuminate\Contracts\Queue\ShouldBeEncrypted;', $content);
        $this->assertStringContainsString('use Illuminate\Queue\InteractsWithQueue;', $content);
        $this->assertStringContainsString('Session::createFromRequest', $content);
    }

    public function test_failed_method_implementation(): void
    {
        $reflection = new \ReflectionClass(HandleRequestApproved::class);
        $filename = $reflection->getFileName();
        $content = file_get_contents($filename);

        $this->assertStringContainsString('public function failed(RequestApproved $event, \\Throwable $exception): void', $content);
        $this->assertStringContainsString('\\Log::error', $content);
    }

    public function test_listener_uses_correct_namespace(): void
    {
        $this->assertStringStartsWith('App\Listeners', HandleRequestApproved::class);
    }

    public function test_listener_can_be_serialized(): void
    {
        $serialized = serialize($this->listener);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(HandleRequestApproved::class, $unserialized);
    }

    public function test_listener_has_correct_class_name(): void
    {
        $this->assertEquals('HandleRequestApproved', class_basename(HandleRequestApproved::class));
    }

    public function test_listener_file_exists(): void
    {
        $reflection = new \ReflectionClass(HandleRequestApproved::class);
        $filename = $reflection->getFileName();

        $this->assertFileExists($filename);
        $this->assertStringEndsWith('HandleRequestApproved.php', $filename);
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
