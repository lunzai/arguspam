<?php

namespace Lunzai\QuerySearch\Tests\Feature;

use Illuminate\Http\Request;
use Lunzai\QuerySearch\Tests\Fixtures\Models\User;
use Lunzai\QuerySearch\Tests\Fixtures\Search\UserSearch;
use Lunzai\QuerySearch\Tests\TestCase;

class SortingTest extends TestCase
{
    public function test_sort_ascending_by_name(): void
    {
        User::create(['name' => 'Zara', 'email' => 'zara@example.com', 'status' => 'active']);
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);
        User::create(['name' => 'Mike', 'email' => 'mike@example.com', 'status' => 'active']);

        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['sort' => 'name']))
            ->paginate(10);

        $names = $result->pluck('name')->toArray();
        $this->assertEquals(['Alice', 'Mike', 'Zara'], $names);
    }

    public function test_sort_descending_by_created_at(): void
    {
        User::create(['name' => 'Old', 'email' => 'old@example.com', 'status' => 'active', 'created_at' => '2023-01-01']);
        User::create(['name' => 'New', 'email' => 'new@example.com', 'status' => 'active', 'created_at' => '2024-01-01']);

        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['sort' => '-created_at']))
            ->paginate(10);

        $this->assertEquals('new@example.com', $result->first()->email);
    }

    public function test_unknown_sort_field_is_ignored(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);

        // Should not throw; the unknown field is silently dropped.
        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['sort' => 'unknown_column']))
            ->paginate(10);

        $this->assertCount(1, $result);
    }

    public function test_multiple_sort_fields(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active',   'created_at' => '2024-01-01']);
        User::create(['name' => 'Bob',   'email' => 'bob@example.com',   'status' => 'active',   'created_at' => '2024-01-02']);
        User::create(['name' => 'Carol', 'email' => 'carol@example.com', 'status' => 'inactive', 'created_at' => '2024-01-01']);

        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['sort' => 'status,name']))
            ->paginate(10);

        // 'active' < 'inactive' alphabetically; within active: alice < bob
        $emails = $result->pluck('email')->toArray();
        $this->assertEquals(['alice@example.com', 'bob@example.com', 'carol@example.com'], $emails);
    }
}
