<?php

namespace Tests\Unit\Listeners;

use App\Events\SessionAiAudited;
use App\Listeners\HandleSessionAiReviewed;
use App\Models\Asset;
use App\Models\Org;
use App\Models\Request as RequestModel;
use App\Models\Session;
use App\Models\User;
use App\Notifications\SessionReviewOptionalNotifyApprover;
use App\Notifications\SessionReviewOptionalNotifyRequester;
use App\Notifications\SessionReviewRequiredNotifyApprover;
use App\Notifications\SessionReviewRequiredNotifyRequester;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class HandleSessionAiReviewedTest extends TestCase
{
    private HandleSessionAiReviewed $listener;
    private User $requester;
    private User $approver;
    private Org $org;
    private Asset $asset;
    private RequestModel $request;
    private Session $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->listener = new HandleSessionAiReviewed;
        $this->requester = \Mockery::mock(User::class);
        $this->approver = \Mockery::mock(User::class);
        $this->session = \Mockery::mock(Session::class)->makePartial();
    }

    protected function tearDown(): void
    {
        \Mockery::close();
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

    public function test_handle_sends_required_notifications_when_manual_review_required(): void
    {
        // Setup session properties
        $this->session->approver_id = 1;
        $this->session->requester_id = 2;
        $this->session->requester = $this->requester;
        $this->session->approver = $this->approver;

        // Mock session to require manual review
        $this->session->shouldReceive('isRequiredManualReview')->andReturn(true);
        
        // Allow getAttribute to return properties (permissive mock)
        // Store properties in a closure variable to access them
        $sessionProps = [];
        $sessionProps['requester'] = $this->requester;
        $sessionProps['approver'] = $this->approver;
        $sessionProps['approver_id'] = 1;
        $sessionProps['requester_id'] = 2;
        
        $this->session->shouldReceive('getAttribute')
            ->andReturnUsing(function ($key) use ($sessionProps) {
                return $sessionProps[$key] ?? null;
            });

        // Mock User models - verify notify() is called with correct notification types
        $this->requester->shouldReceive('notify')
            ->once()
            ->with(\Mockery::type(SessionReviewRequiredNotifyRequester::class))
            ->andReturn(true);
        $this->approver->shouldReceive('notify')
            ->once()
            ->with(\Mockery::type(SessionReviewRequiredNotifyApprover::class))
            ->andReturn(true);

        $event = new SessionAiAudited($this->session);
        $this->listener->handle($event);
        
        // Explicit assertion - Mockery expectations are verified in tearDown()
        $this->addToAssertionCount(2); // One for each notify() expectation
    }

    public function test_handle_sends_optional_notifications_when_manual_review_not_required(): void
    {
        // Setup session properties
        $this->session->approver_id = 1;
        $this->session->requester_id = 2;
        $this->session->requester = $this->requester;
        $this->session->approver = $this->approver;

        // Mock session to not require manual review
        $this->session->shouldReceive('isRequiredManualReview')->andReturn(false);
        
        // Allow getAttribute to return properties (permissive mock)
        // Store properties in a closure variable to access them
        $sessionProps = [];
        $sessionProps['requester'] = $this->requester;
        $sessionProps['approver'] = $this->approver;
        $sessionProps['approver_id'] = 1;
        $sessionProps['requester_id'] = 2;
        
        $this->session->shouldReceive('getAttribute')
            ->andReturnUsing(function ($key) use ($sessionProps) {
                return $sessionProps[$key] ?? null;
            });

        // Mock User models - verify notify() is called with correct notification types
        $this->requester->shouldReceive('notify')
            ->once()
            ->with(\Mockery::type(SessionReviewOptionalNotifyRequester::class))
            ->andReturn(true);
        $this->approver->shouldReceive('notify')
            ->once()
            ->with(\Mockery::type(SessionReviewOptionalNotifyApprover::class))
            ->andReturn(true);

        $event = new SessionAiAudited($this->session);
        $this->listener->handle($event);
        
        // Explicit assertion - Mockery expectations are verified in tearDown()
        $this->addToAssertionCount(2); // One for each notify() expectation
    }

    public function test_handle_does_not_notify_approver_when_approver_is_same_as_requester(): void
    {
        // Set approver same as requester
        $this->session->approver_id = 1;
        $this->session->requester_id = 1;
        $this->session->requester = $this->requester;
        $this->session->approver = $this->approver;
        $this->session->shouldReceive('isRequiredManualReview')->andReturn(true);

        // Allow getAttribute to return properties (permissive mock)
        // Store properties in a closure variable to access them
        $sessionProps = [];
        $sessionProps['requester'] = $this->requester;
        $sessionProps['approver'] = $this->approver;
        $sessionProps['approver_id'] = 1;
        $sessionProps['requester_id'] = 1;
        
        $this->session->shouldReceive('getAttribute')
            ->andReturnUsing(function ($key) use ($sessionProps) {
                return $sessionProps[$key] ?? null;
            });

        // Mock User model - verify requester is notified but approver is not
        $this->requester->shouldReceive('notify')
            ->once()
            ->with(\Mockery::type(SessionReviewRequiredNotifyRequester::class))
            ->andReturn(true);
        $this->approver->shouldReceive('notify')->never();

        $event = new SessionAiAudited($this->session);
        $this->listener->handle($event);
        
        // Explicit assertion - Mockery expectations are verified in tearDown()
        $this->addToAssertionCount(2); // One for each notify() expectation
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
