<?php

namespace Lunzai\QuerySearch\Tests\Unit\Filters;

use Lunzai\QuerySearch\Filters\RangeFilter;
use Lunzai\QuerySearch\Tests\Fixtures\Models\User;
use Lunzai\QuerySearch\Tests\TestCase;

class RangeFilterTest extends TestCase
{
    public function test_applies_where_between_for_full_range(): void
    {
        User::create(['name' => 'Old',  'email' => 'old@example.com',  'status' => 'active', 'created_at' => '2023-06-01']);
        User::create(['name' => 'In',   'email' => 'in@example.com',   'status' => 'active', 'created_at' => '2024-06-01']);
        User::create(['name' => 'New',  'email' => 'new@example.com',  'status' => 'active', 'created_at' => '2025-06-01']);

        $filter = new RangeFilter;
        $results = $filter->apply(User::query(), 'created_at', '2024-01-01,2024-12-31')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('in@example.com', $results->first()->email);
    }

    public function test_applies_gte_when_only_min_provided(): void
    {
        User::create(['name' => 'Old', 'email' => 'old@example.com', 'status' => 'active', 'created_at' => '2023-06-01']);
        User::create(['name' => 'New', 'email' => 'new@example.com', 'status' => 'active', 'created_at' => '2024-06-01']);

        $filter = new RangeFilter;
        $results = $filter->apply(User::query(), 'created_at', '2024-01-01,')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('new@example.com', $results->first()->email);
    }

    public function test_applies_lte_when_only_max_provided(): void
    {
        User::create(['name' => 'Old', 'email' => 'old@example.com', 'status' => 'active', 'created_at' => '2023-06-01']);
        User::create(['name' => 'New', 'email' => 'new@example.com', 'status' => 'active', 'created_at' => '2024-06-01']);

        $filter = new RangeFilter;
        $results = $filter->apply(User::query(), 'created_at', ',2023-12-31')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('old@example.com', $results->first()->email);
    }

    public function test_skips_when_no_comma_present(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active', 'created_at' => '2024-06-01']);

        $filter = new RangeFilter;
        $results = $filter->apply(User::query(), 'created_at', '2024-01-01')->get();

        // No comma → filter skipped → all records returned.
        $this->assertCount(1, $results);
    }

    public function test_skips_when_both_sides_empty(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'active']);
        User::create(['name' => 'Bob',   'email' => 'bob@example.com',   'status' => 'active']);

        $filter = new RangeFilter;
        $results = $filter->apply(User::query(), 'created_at', ',')->get();

        // Both sides empty → filter skipped → all records returned.
        $this->assertCount(2, $results);
    }
}
