<?php

namespace Lunzai\QuerySearch\Tests\Unit;

use Illuminate\Http\Request;
use LogicException;
use Lunzai\QuerySearch\Tests\Fixtures\Models\User;
use Lunzai\QuerySearch\Tests\Fixtures\Search\UserSearch;
use Lunzai\QuerySearch\Tests\TestCase;

class SearchValidationTest extends TestCase
{
    public function test_apply_without_for_throws_logic_exception(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Call for() before apply() or paginate().');

        (new UserSearch)->apply();
    }

    public function test_paginate_without_for_throws_logic_exception(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Call for() before apply() or paginate().');

        (new UserSearch)->fromRequest(Request::create('/'))->paginate();
    }

    public function test_apply_is_idempotent_when_called_multiple_times(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);
        User::create(['name' => 'Bob', 'email' => 'bob@example.com', 'status' => 'inactive']);

        $search = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['filter' => ['status' => 'active']]));

        $builder = $search->apply();

        // Second call must return the same builder without re-applying filters.
        $builderAgain = $search->apply();

        $this->assertSame($builder, $builderAgain);

        // Only one WHERE clause from the status filter, not two.
        $sql = $builder->toRawSql();
        $this->assertEquals(1, substr_count($sql, '"status"'));
    }

    public function test_paginate_after_apply_does_not_double_apply_filters(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);
        User::create(['name' => 'Bob', 'email' => 'bob@example.com', 'status' => 'inactive']);

        $search = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['filter' => ['status' => 'active']]));

        // Manually apply, then paginate — should still return only 1 result.
        $search->apply();
        $result = $search->paginate(10);

        $this->assertCount(1, $result);
        $this->assertEquals('alice@example.com', $result->first()->email);
    }
}
