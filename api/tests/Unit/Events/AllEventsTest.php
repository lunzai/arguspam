<?php

namespace Tests\Unit\Events;

use App\Events\RequestApproved;
use App\Events\RequestCancelled;
use App\Events\RequestCreated;
use App\Events\RequestExpired;
use App\Events\RequestRejected;
use App\Events\RequestSubmitted;
use App\Events\SessionAiAudited;
use App\Events\SessionCancelled;
use App\Events\SessionCreated;
use App\Events\SessionEnded;
use App\Events\SessionExpired;
use App\Events\SessionStarted;
use App\Events\SessionTerminated;
use App\Events\UserLoggedIn;
use App\Models\Request;
use App\Models\Session;
use App\Models\User;
use Tests\TestCase;

class AllEventsTest extends TestCase
{
    public function test_all_events_have_dispatchable_trait(): void
    {
        $events = [
            RequestCreated::class,
            RequestApproved::class,
            RequestCancelled::class,
            RequestExpired::class,
            RequestRejected::class,
            RequestSubmitted::class,
            SessionCreated::class,
            SessionStarted::class,
            SessionEnded::class,
            SessionCancelled::class,
            SessionExpired::class,
            SessionTerminated::class,
            SessionAiAudited::class,
            UserLoggedIn::class,
        ];

        foreach ($events as $eventClass) {
            $this->assertTrue(
                in_array('Illuminate\Foundation\Events\Dispatchable', class_uses($eventClass)),
                "Event {$eventClass} should use Dispatchable trait"
            );
        }
    }

    public function test_all_events_have_serializes_models_trait(): void
    {
        $events = [
            RequestCreated::class,
            RequestApproved::class,
            RequestCancelled::class,
            RequestExpired::class,
            RequestRejected::class,
            RequestSubmitted::class,
            SessionCreated::class,
            SessionStarted::class,
            SessionEnded::class,
            SessionCancelled::class,
            SessionExpired::class,
            SessionTerminated::class,
            SessionAiAudited::class,
            UserLoggedIn::class,
        ];

        foreach ($events as $eventClass) {
            $this->assertTrue(
                in_array('Illuminate\Queue\SerializesModels', class_uses($eventClass)),
                "Event {$eventClass} should use SerializesModels trait"
            );
        }
    }

    public function test_request_events_can_be_instantiated(): void
    {
        $request = $this->createMock(Request::class);
        $request->id = 1;
        $request->reason = 'Test request';
        $request->status = 'pending';

        $events = [
            RequestCreated::class,
            RequestApproved::class,
            RequestCancelled::class,
            RequestExpired::class,
            RequestRejected::class,
            RequestSubmitted::class,
        ];

        foreach ($events as $eventClass) {
            $event = new $eventClass($request);
            $this->assertInstanceOf($eventClass, $event);
            $this->assertEquals($request->id, $event->request->id);
        }
    }

    public function test_session_events_can_be_instantiated(): void
    {
        $session = $this->createMock(Session::class);
        $session->id = 1;
        $session->status = 'scheduled';

        // Events with just session parameter
        $simpleSessionEvents = [
            SessionCreated::class,
            SessionCancelled::class,
            SessionExpired::class,
            SessionTerminated::class,
            SessionAiAudited::class,
        ];

        foreach ($simpleSessionEvents as $eventClass) {
            $event = new $eventClass($session);
            $this->assertInstanceOf($eventClass, $event);
            $this->assertEquals($session->id, $event->session->id);
        }

        // Events with additional parameters
        $credentials = ['username' => 'test', 'password' => 'test'];
        $terminationResults = ['success' => true];

        $sessionStartedEvent = new SessionStarted($session, $credentials);
        $this->assertInstanceOf(SessionStarted::class, $sessionStartedEvent);
        $this->assertEquals($session->id, $sessionStartedEvent->session->id);
        $this->assertEquals($credentials, $sessionStartedEvent->credentials);

        $sessionEndedEvent = new SessionEnded($session, $terminationResults);
        $this->assertInstanceOf(SessionEnded::class, $sessionEndedEvent);
        $this->assertEquals($session->id, $sessionEndedEvent->session->id);
        $this->assertEquals($terminationResults, $sessionEndedEvent->terminationResults);
    }

    public function test_user_logged_in_event_can_be_instantiated(): void
    {
        $user = $this->createMock(User::class);
        $user->id = 1;
        $user->name = 'Test User';
        $user->email = 'test@example.com';

        $event = new UserLoggedIn($user);

        $this->assertInstanceOf(UserLoggedIn::class, $event);
        $this->assertEquals($user->id, $event->user->id);
        $this->assertEquals($user->name, $event->user->name);
        $this->assertEquals($user->email, $event->user->email);
    }

    public function test_all_events_have_interacts_with_sockets_trait(): void
    {
        $eventsWithSockets = [
            RequestCreated::class,
            RequestApproved::class,
            RequestCancelled::class,
            RequestExpired::class,
            RequestRejected::class,
            RequestSubmitted::class,
            SessionCreated::class,
            SessionStarted::class,
            SessionEnded::class,
            SessionCancelled::class,
            SessionExpired::class,
            SessionTerminated::class,
            SessionAiAudited::class,
        ];

        foreach ($eventsWithSockets as $eventClass) {
            $this->assertTrue(
                in_array('Illuminate\Broadcasting\InteractsWithSockets', class_uses($eventClass)),
                "Event {$eventClass} should use InteractsWithSockets trait"
            );
        }

        // UserLoggedIn doesn't have InteractsWithSockets
        $this->assertFalse(
            in_array('Illuminate\Broadcasting\InteractsWithSockets', class_uses(UserLoggedIn::class)),
            'UserLoggedIn should NOT use InteractsWithSockets trait'
        );
    }

    public function test_events_have_dispatch_method(): void
    {
        // Test that dispatch method exists on event classes
        $this->assertTrue(method_exists(RequestCreated::class, 'dispatch'));
        $this->assertTrue(method_exists(SessionCreated::class, 'dispatch'));
        $this->assertTrue(method_exists(UserLoggedIn::class, 'dispatch'));
    }

    public function test_event_properties_are_public(): void
    {
        $request = $this->createMock(Request::class);
        $request->id = 1;

        $session = $this->createMock(Session::class);
        $session->id = 1;

        $user = $this->createMock(User::class);
        $user->id = 1;

        $requestEvent = new RequestCreated($request);
        $sessionEvent = new SessionCreated($session);
        $userEvent = new UserLoggedIn($user);

        // Test that properties are accessible
        $this->assertObjectHasProperty('request', $requestEvent);
        $this->assertObjectHasProperty('session', $sessionEvent);
        $this->assertObjectHasProperty('user', $userEvent);
    }

    public function test_session_events_with_additional_data(): void
    {
        $session = $this->createMock(Session::class);
        $session->id = 1;

        // Test SessionStarted with credentials
        $credentials = [
            'username' => 'admin',
            'password' => 'secret',
            'host' => '192.168.1.1',
            'port' => 3306,
        ];

        $sessionStartedEvent = new SessionStarted($session, $credentials);
        $this->assertEquals($credentials, $sessionStartedEvent->credentials);
        $this->assertEquals('admin', $sessionStartedEvent->credentials['username']);
        $this->assertEquals('secret', $sessionStartedEvent->credentials['password']);

        // Test SessionEnded with termination results
        $terminationResults = [
            'success' => true,
            'message' => 'Session ended successfully',
            'duration' => 3600,
        ];

        $sessionEndedEvent = new SessionEnded($session, $terminationResults);
        $this->assertEquals($terminationResults, $sessionEndedEvent->terminationResults);
        $this->assertTrue($sessionEndedEvent->terminationResults['success']);
        $this->assertEquals('Session ended successfully', $sessionEndedEvent->terminationResults['message']);
    }
}
