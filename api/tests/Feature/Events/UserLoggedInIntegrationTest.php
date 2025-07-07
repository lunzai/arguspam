<?php

namespace Tests\Feature\Events;

use App\Events\UserLoggedIn;
use App\Listeners\UpdateUserLastLogin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UserLoggedInIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_is_dispatched_when_user_logs_in()
    {
        Event::fake([UserLoggedIn::class]);

        $user = User::factory()->password('password123')->create();

        $response = $this->postJson('/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200);

        Event::assertDispatched(UserLoggedIn::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    public function test_listener_is_called_when_event_is_dispatched()
    {
        $user = User::factory()->password('password123')->create([
            'last_login_at' => null,
        ]);

        $this->assertNull($user->last_login_at);

        $response = $this->postJson('/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->last_login_at);
    }

    public function test_multiple_logins_update_last_login_at()
    {
        $user = User::factory()->password('password123')->create([
            'last_login_at' => null,
        ]);

        $response1 = $this->postJson('/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response1->assertStatus(200);
        $user->refresh();
        $firstLoginTime = $user->last_login_at;

        $this->assertNotNull($firstLoginTime);

        sleep(1);

        $response2 = $this->postJson('/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response2->assertStatus(200);
        $user->refresh();
        $secondLoginTime = $user->last_login_at;

        $this->assertNotNull($secondLoginTime);
        $this->assertTrue($secondLoginTime->isAfter($firstLoginTime));
    }

    public function test_event_dispatching_works_with_event_facade()
    {
        Event::fake([UserLoggedIn::class]);

        $user = User::factory()->create();

        UserLoggedIn::dispatch($user);

        Event::assertDispatched(UserLoggedIn::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    public function test_listener_handles_event_correctly()
    {
        $user = User::factory()->create(['last_login_at' => null]);

        $event = new UserLoggedIn($user);
        $listener = new UpdateUserLastLogin;

        $listener->handle($event);

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
    }

    public function test_failed_login_does_not_dispatch_event()
    {
        Event::fake([UserLoggedIn::class]);

        $user = User::factory()->password('correctpassword')->create();

        $response = $this->postJson('/auth/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);

        Event::assertNotDispatched(UserLoggedIn::class);
    }

    public function test_login_with_invalid_email_does_not_dispatch_event()
    {
        Event::fake([UserLoggedIn::class]);

        $response = $this->postJson('/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);

        Event::assertNotDispatched(UserLoggedIn::class);
    }
}
