<?php

namespace Tests\Unit\Listeners;

use App\Listeners\HandleRequestApproved;
use App\Listeners\HandleRequestCancelled;
use App\Listeners\HandleRequestCreated;
use App\Listeners\HandleRequestExpired;
use App\Listeners\HandleRequestRejected;
use App\Listeners\HandleRequestSubmitted;
use App\Listeners\HandleSessionAiReviewed;
use App\Listeners\HandleSessionCancelled;
use App\Listeners\HandleSessionCreated;
use App\Listeners\HandleSessionEnded;
use App\Listeners\HandleSessionExpired;
use App\Listeners\HandleSessionStarted;
use App\Listeners\HandleSessionTerminated;
use App\Listeners\UpdateUserLastLogin;
use Tests\TestCase;

class AllListenersTest extends TestCase
{
    public function test_all_listeners_can_be_instantiated(): void
    {
        $listeners = [
            HandleRequestApproved::class,
            HandleRequestCancelled::class,
            HandleRequestExpired::class,
            HandleRequestRejected::class,
            HandleRequestSubmitted::class,
            HandleSessionAiReviewed::class,
            HandleSessionCancelled::class,
            HandleSessionCreated::class,
            HandleSessionEnded::class,
            HandleSessionExpired::class,
            HandleSessionStarted::class,
            HandleSessionTerminated::class,
            UpdateUserLastLogin::class,
        ];

        foreach ($listeners as $listenerClass) {
            $listener = new $listenerClass;
            $this->assertInstanceOf($listenerClass, $listener);
        }

        // Test HandleRequestCreated separately as it requires constructor parameter
        $handleRequestCreated = new HandleRequestCreated(\Mockery::mock(\App\Services\OpenAI\OpenAiService::class));
        $this->assertInstanceOf(HandleRequestCreated::class, $handleRequestCreated);
    }

    public function test_all_listeners_have_handle_method(): void
    {
        $listeners = [
            HandleRequestApproved::class,
            HandleRequestCancelled::class,
            HandleRequestCreated::class,
            HandleRequestExpired::class,
            HandleRequestRejected::class,
            HandleRequestSubmitted::class,
            HandleSessionAiReviewed::class,
            HandleSessionCancelled::class,
            HandleSessionCreated::class,
            HandleSessionEnded::class,
            HandleSessionExpired::class,
            HandleSessionStarted::class,
            HandleSessionTerminated::class,
            UpdateUserLastLogin::class,
        ];

        foreach ($listeners as $listenerClass) {
            $this->assertTrue(method_exists($listenerClass, 'handle'));
        }
    }

    public function test_handle_methods_are_public(): void
    {
        $listeners = [
            HandleRequestApproved::class,
            HandleRequestCancelled::class,
            HandleRequestCreated::class,
            HandleRequestExpired::class,
            HandleRequestRejected::class,
            HandleRequestSubmitted::class,
            HandleSessionAiReviewed::class,
            HandleSessionCancelled::class,
            HandleSessionCreated::class,
            HandleSessionEnded::class,
            HandleSessionExpired::class,
            HandleSessionStarted::class,
            HandleSessionTerminated::class,
            UpdateUserLastLogin::class,
        ];

        foreach ($listeners as $listenerClass) {
            $reflection = new \ReflectionClass($listenerClass);
            $handleMethod = $reflection->getMethod('handle');
            $this->assertTrue($handleMethod->isPublic());
        }
    }

    public function test_handle_methods_return_void(): void
    {
        $listeners = [
            HandleRequestApproved::class,
            HandleRequestCancelled::class,
            HandleRequestCreated::class,
            HandleRequestExpired::class,
            HandleRequestRejected::class,
            HandleRequestSubmitted::class,
            HandleSessionAiReviewed::class,
            HandleSessionCancelled::class,
            HandleSessionCreated::class,
            HandleSessionEnded::class,
            HandleSessionExpired::class,
            HandleSessionStarted::class,
            HandleSessionTerminated::class,
            UpdateUserLastLogin::class,
        ];

        foreach ($listeners as $listenerClass) {
            $reflection = new \ReflectionClass($listenerClass);
            $handleMethod = $reflection->getMethod('handle');
            $returnType = $handleMethod->getReturnType();
            $this->assertNotNull($returnType);
            $this->assertEquals('void', $returnType->getName());
        }
    }

    public function test_handle_methods_accept_correct_event_parameters(): void
    {
        $listenerEventMap = [
            HandleRequestApproved::class => 'App\Events\RequestApproved',
            HandleRequestCancelled::class => 'App\Events\RequestCancelled',
            HandleRequestCreated::class => 'App\Events\RequestCreated',
            HandleRequestExpired::class => 'App\Events\RequestExpired',
            HandleRequestRejected::class => 'App\Events\RequestRejected',
            HandleRequestSubmitted::class => 'App\Events\RequestSubmitted',
            HandleSessionAiReviewed::class => 'App\Events\SessionAiReviewed',
            HandleSessionCancelled::class => 'App\Events\SessionCancelled',
            HandleSessionCreated::class => 'App\Events\SessionCreated',
            HandleSessionEnded::class => 'App\Events\SessionEnded',
            HandleSessionExpired::class => 'App\Events\SessionExpired',
            HandleSessionStarted::class => 'App\Events\SessionStarted',
            HandleSessionTerminated::class => 'App\Events\SessionTerminated',
            UpdateUserLastLogin::class => 'App\Events\UserLoggedIn',
        ];

        foreach ($listenerEventMap as $listenerClass => $expectedEventClass) {
            $reflection = new \ReflectionClass($listenerClass);
            $handleMethod = $reflection->getMethod('handle');
            $parameters = $handleMethod->getParameters();

            $this->assertCount(1, $parameters);
            $parameter = $parameters[0];
            $this->assertEquals($expectedEventClass, $parameter->getType()->getName());
        }
    }

    public function test_queued_listeners_implement_should_queue(): void
    {
        $queuedListeners = [
            HandleRequestCreated::class,
            HandleSessionStarted::class,
            HandleSessionEnded::class,
            HandleSessionCancelled::class,
            HandleSessionExpired::class,
            HandleSessionTerminated::class,
            HandleSessionCreated::class,
            HandleSessionAiReviewed::class,
        ];

        foreach ($queuedListeners as $listenerClass) {
            $this->assertTrue(
                in_array('Illuminate\Contracts\Queue\ShouldQueue', class_implements($listenerClass)),
                "Listener {$listenerClass} should implement ShouldQueue"
            );
        }
    }

    public function test_encrypted_listeners_implement_should_be_encrypted(): void
    {
        $encryptedListeners = [
            HandleRequestCreated::class,
        ];

        foreach ($encryptedListeners as $listenerClass) {
            $this->assertTrue(
                in_array('Illuminate\Contracts\Queue\ShouldBeEncrypted', class_implements($listenerClass)),
                "Listener {$listenerClass} should implement ShouldBeEncrypted"
            );
        }
    }

    public function test_queued_listeners_have_interacts_with_queue_trait(): void
    {
        $queuedListeners = [
            HandleRequestCreated::class,
            HandleSessionStarted::class,
            HandleSessionEnded::class,
            HandleSessionCancelled::class,
            HandleSessionExpired::class,
            HandleSessionTerminated::class,
            HandleSessionCreated::class,
            HandleSessionAiReviewed::class,
        ];

        foreach ($queuedListeners as $listenerClass) {
            $this->assertTrue(
                in_array('Illuminate\Queue\InteractsWithQueue', class_uses($listenerClass)),
                "Listener {$listenerClass} should use InteractsWithQueue trait"
            );
        }
    }

    public function test_listeners_have_correct_namespace(): void
    {
        $listeners = [
            HandleRequestApproved::class,
            HandleRequestCancelled::class,
            HandleRequestCreated::class,
            HandleRequestExpired::class,
            HandleRequestRejected::class,
            HandleRequestSubmitted::class,
            HandleSessionAiReviewed::class,
            HandleSessionCancelled::class,
            HandleSessionCreated::class,
            HandleSessionEnded::class,
            HandleSessionExpired::class,
            HandleSessionStarted::class,
            HandleSessionTerminated::class,
            UpdateUserLastLogin::class,
        ];

        foreach ($listeners as $listenerClass) {
            $this->assertStringStartsWith('App\Listeners', $listenerClass);
        }
    }

    public function test_listeners_can_be_serialized(): void
    {
        $listeners = [
            new HandleRequestApproved,
            new HandleRequestCancelled,
            new HandleRequestExpired,
            new HandleRequestRejected,
            new HandleRequestSubmitted,
            new HandleSessionAiReviewed,
            new HandleSessionCancelled,
            new HandleSessionCreated,
            new HandleSessionEnded,
            new HandleSessionExpired,
            new HandleSessionStarted,
            new HandleSessionTerminated,
            new UpdateUserLastLogin,
        ];

        foreach ($listeners as $listener) {
            $serialized = serialize($listener);
            $unserialized = unserialize($serialized);
            $this->assertInstanceOf(get_class($listener), $unserialized);
        }
    }

    public function test_listeners_have_handle_method_with_correct_signature(): void
    {
        $listeners = [
            HandleRequestApproved::class,
            HandleRequestCancelled::class,
            HandleRequestCreated::class,
            HandleRequestExpired::class,
            HandleRequestRejected::class,
            HandleRequestSubmitted::class,
            HandleSessionAiReviewed::class,
            HandleSessionCancelled::class,
            HandleSessionCreated::class,
            HandleSessionEnded::class,
            HandleSessionExpired::class,
            HandleSessionStarted::class,
            HandleSessionTerminated::class,
            UpdateUserLastLogin::class,
        ];

        foreach ($listeners as $listenerClass) {
            $reflection = new \ReflectionClass($listenerClass);
            $handleMethod = $reflection->getMethod('handle');

            // Check method signature
            $this->assertTrue($handleMethod->isPublic());
            $this->assertEquals('void', $handleMethod->getReturnType()->getName());
            $this->assertCount(1, $handleMethod->getParameters());
        }
    }
}
