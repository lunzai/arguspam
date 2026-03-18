<?php

namespace Lunzai\QuerySearch\Tests\Unit\Filters;

use Lunzai\QuerySearch\Filters\InFilter;
use Lunzai\QuerySearch\Tests\Fixtures\Models\User;
use Lunzai\QuerySearch\Tests\TestCase;

class InFilterTest extends TestCase
{
    public function test_single_value_applies_where(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);
        User::create(['name' => 'Bob',   'email' => 'bob@example.com',   'status' => 'inactive']);

        $filter = new InFilter;
        $results = $filter->apply(User::query(), 'status', 'active')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('alice@example.com', $results->first()->email);
    }

    public function test_multiple_values_applies_where_in(): void
    {
        User::create(['name' => 'Alice',   'email' => 'alice@example.com',   'status' => 'active']);
        User::create(['name' => 'Bob',     'email' => 'bob@example.com',     'status' => 'inactive']);
        User::create(['name' => 'Charlie', 'email' => 'charlie@example.com', 'status' => 'suspended']);

        $filter = new InFilter;
        $results = $filter->apply(User::query(), 'status', 'active,inactive')->get();

        $this->assertCount(2, $results);
    }

    public function test_strips_whitespace_around_values(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);
        User::create(['name' => 'Bob',   'email' => 'bob@example.com',   'status' => 'inactive']);

        $filter = new InFilter;
        $results = $filter->apply(User::query(), 'status', ' active , inactive ')->get();

        $this->assertCount(2, $results);
    }

    public function test_skips_empty_string(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);

        $filter = new InFilter;
        $results = $filter->apply(User::query(), 'status', '')->get();

        // Empty value — no filter applied, all records returned.
        $this->assertCount(1, $results);
    }

    public function test_skips_only_commas(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);
        User::create(['name' => 'Bob',   'email' => 'bob@example.com',   'status' => 'inactive']);

        $filter = new InFilter;
        $results = $filter->apply(User::query(), 'status', ',,,')->get();

        // All-comma value — no filter applied, all records returned.
        $this->assertCount(2, $results);
    }
}
