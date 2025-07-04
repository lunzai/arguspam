<?php

namespace Tests\Unit\Listeners;

use App\Events\UserLoggedIn;
use App\Listeners\UpdateUserLastLogin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateUserLastLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_listener_can_be_instantiated()
    {
        $listener = new UpdateUserLastLogin();
        
        $this->assertInstanceOf(UpdateUserLastLogin::class, $listener);
    }

    public function test_handle_updates_user_last_login_at()
    {
        $user = User::factory()->create([
            'last_login_at' => null
        ]);
        
        $this->assertNull($user->last_login_at);
        
        $event = new UserLoggedIn($user);
        $listener = new UpdateUserLastLogin();
        
        $listener->handle($event);
        
        $user->refresh();
        $this->assertNotNull($user->last_login_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->last_login_at);
    }

    public function test_handle_updates_existing_last_login_at()
    {
        $oldLoginTime = now()->subDays(1);
        $user = User::factory()->create([
            'last_login_at' => $oldLoginTime
        ]);
        
        $this->assertEquals($oldLoginTime->toDateTimeString(), $user->last_login_at->toDateTimeString());
        
        $event = new UserLoggedIn($user);
        $listener = new UpdateUserLastLogin();
        
        $listener->handle($event);
        
        $user->refresh();
        $this->assertNotEquals($oldLoginTime->toDateTimeString(), $user->last_login_at->toDateTimeString());
        $this->assertTrue($user->last_login_at->isAfter($oldLoginTime));
    }

    public function test_handle_method_returns_void()
    {
        $user = User::factory()->create();
        $event = new UserLoggedIn($user);
        $listener = new UpdateUserLastLogin();
        
        $result = $listener->handle($event);
        
        $this->assertNull($result);
    }

    public function test_handle_preserves_other_user_data()
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'last_login_at' => null
        ]);
        
        $originalName = $user->name;
        $originalEmail = $user->email;
        
        $event = new UserLoggedIn($user);
        $listener = new UpdateUserLastLogin();
        
        $listener->handle($event);
        
        $user->refresh();
        $this->assertEquals($originalName, $user->name);
        $this->assertEquals($originalEmail, $user->email);
        $this->assertNotNull($user->last_login_at);
    }
}