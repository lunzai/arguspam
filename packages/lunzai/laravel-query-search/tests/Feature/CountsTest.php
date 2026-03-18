<?php

namespace Lunzai\QuerySearch\Tests\Feature;

use Illuminate\Http\Request;
use Lunzai\QuerySearch\Tests\Fixtures\Models\User;
use Lunzai\QuerySearch\Tests\Fixtures\Models\UserSession;
use Lunzai\QuerySearch\Tests\Fixtures\Search\UserSearch;
use Lunzai\QuerySearch\Tests\TestCase;

class CountsTest extends TestCase
{
    public function test_count_loads_relation_count(): void
    {
        $user = User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);
        UserSession::create(['user_id' => $user->id]);
        UserSession::create(['user_id' => $user->id]);

        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['count' => 'sessions']))
            ->paginate(10);

        $first = $result->first();
        $this->assertEquals(2, $first->sessions_count);
    }

    public function test_count_ignores_unknown_relation(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);

        // 'roles' is not in $countable — should be silently ignored.
        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['count' => 'roles']))
            ->paginate(10);

        $first = $result->first();
        $this->assertFalse(isset($first->roles_count));
    }
}
