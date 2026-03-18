<?php

namespace Lunzai\QuerySearch\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Lunzai\QuerySearch\Tests\Fixtures\Models\User;
use Lunzai\QuerySearch\Tests\Fixtures\Search\UserSearch;
use Lunzai\QuerySearch\Tests\TestCase;

class LenientBehaviorTest extends TestCase
{
    public function test_invalid_mfa_value_is_ignored_not_422(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active',
            'two_factor_enabled' => true, 'two_factor_confirmed_at' => now()]);
        User::create(['name' => 'Bob', 'email' => 'bob@example.com', 'status' => 'active',
            'two_factor_enabled' => false]);

        // 'unknown' is not in the regex — should be dropped, so all users returned.
        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['filter' => ['mfa' => 'unknown']]))
            ->paginate(10);

        // Filter was dropped → all 2 users returned.
        $this->assertCount(2, $result);
    }

    public function test_invalid_sort_field_is_ignored(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);
        User::create(['name' => 'Bob',   'email' => 'bob@example.com',   'status' => 'active']);

        // 'hacked_field' is not in $sortable — silently ignored.
        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['sort' => 'hacked_field']))
            ->paginate(10);

        $this->assertCount(2, $result);
    }

    public function test_unknown_include_is_ignored(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);

        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['include' => 'unknown_relation']))
            ->paginate(10);

        $this->assertCount(1, $result);
    }

    public function test_valid_and_invalid_filters_coexist(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);
        User::create(['name' => 'Bob',   'email' => 'bob@example.com',   'status' => 'inactive']);

        // status=active is valid; mfa=badvalue fails validation → dropped
        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['filter' => [
                'status' => 'active',
                'mfa' => 'badvalue',
            ]]))
            ->paginate(10);

        // Only status filter applied → 1 result.
        $this->assertCount(1, $result);
        $this->assertEquals('alice@example.com', $result->first()->email);
    }

    public function test_strict_mode_throws_validation_exception(): void
    {
        config()->set('query-search.strict', true);

        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);

        $this->expectException(ValidationException::class);

        (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['filter' => ['mfa' => 'badvalue']]))
            ->paginate(10);
    }

    public function test_undeclared_filter_key_is_ignored(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);
        User::create(['name' => 'Bob',   'email' => 'bob@example.com',   'status' => 'active']);

        // 'hacked_column' is not declared in filters() — should be ignored entirely.
        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['filter' => ['hacked_column' => '1 OR 1=1']]))
            ->paginate(10);

        $this->assertCount(2, $result);
    }

    public function test_invalid_page_is_ignored(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);

        // page=-5 fails min:1 → dropped → defaults to page 1
        $result = (new UserSearch)
            ->for(User::query())
            ->fromRequest(Request::create('/', 'GET', ['page' => '-5']))
            ->paginate(10);

        $this->assertCount(1, $result);
    }
}
