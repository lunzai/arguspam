<?php

namespace Tests\Unit\Events;

use App\Events\UserLoggedIn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserLoggedInTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_can_be_instantiated()
    {
        $user = User::factory()->create();
        
        $event = new UserLoggedIn($user);
        
        $this->assertInstanceOf(UserLoggedIn::class, $event);
        $this->assertEquals($user->id, $event->user->id);
        $this->assertEquals($user->email, $event->user->email);
    }

    public function test_event_has_dispatchable_trait()
    {
        $this->assertTrue(in_array('Illuminate\Foundation\Events\Dispatchable', class_uses(UserLoggedIn::class)));
    }

    public function test_event_has_serializes_models_trait()
    {
        $this->assertTrue(in_array('Illuminate\Queue\SerializesModels', class_uses(UserLoggedIn::class)));
    }

    public function test_event_can_be_dispatched()
    {
        $user = User::factory()->create();
        
        $result = UserLoggedIn::dispatch($user);
        
        $this->assertIsArray($result);
    }

    public function test_event_user_property_is_accessible()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);
        
        $event = new UserLoggedIn($user);
        
        $this->assertEquals('Test User', $event->user->name);
        $this->assertEquals('test@example.com', $event->user->email);
        $this->assertEquals($user->id, $event->user->id);
    }
}