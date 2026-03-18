<?php

namespace Lunzai\QuerySearch\Tests\Feature;

use Illuminate\Http\Request;
use Lunzai\QuerySearch\Tests\Fixtures\Models\Role;
use Lunzai\QuerySearch\Tests\Fixtures\Models\User;
use Lunzai\QuerySearch\Tests\Fixtures\Search\UserSearch;
use Lunzai\QuerySearch\Tests\TestCase;

class IncludesTest extends TestCase
{
    public function test_includes_allowed_relation(): void
    {
        $role = Role::create(['name' => 'Admin']);
        $user = User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);
        $user->roles()->attach($role);

        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['include' => 'roles']))
            ->paginate(10);

        $first = $result->first();
        $this->assertTrue($first->relationLoaded('roles'));
        $this->assertCount(1, $first->roles);
        $this->assertEquals('Admin', $first->roles->first()->name);
    }

    public function test_ignores_unknown_relation(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);

        // Should not throw for an unknown relation — it is silently filtered out.
        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['include' => 'permissions']))
            ->paginate(10);

        $this->assertCount(1, $result);
        $this->assertFalse($result->first()->relationLoaded('permissions'));
    }

    public function test_ignores_arbitrary_relation_not_in_includeable(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);

        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['include' => 'sessions']))
            ->paginate(10);

        // 'sessions' is in countable but NOT in includeable — should be ignored.
        $this->assertFalse($result->first()->relationLoaded('sessions'));
    }
}
