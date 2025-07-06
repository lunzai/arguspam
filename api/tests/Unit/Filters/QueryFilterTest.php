<?php

namespace Tests\Unit\Filters;

use App\Http\Filters\QueryFilter;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class QueryFilterTest extends TestCase
{
    use RefreshDatabase;

    private QueryFilter $filter;
    private Builder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = User::query();
    }

    private function createFilter(array $params = []): QueryFilter
    {
        $request = new Request($params);
        return new class($request) extends QueryFilter {
            protected array $sortable = ['name', 'email', 'created_at'];

            public function name(string $value): Builder
            {
                return $this->filterLike('name', $value);
            }

            public function email(string $value): Builder
            {
                return $this->filterEqual('email', $value);
            }

            public function status(string $value): Builder
            {
                return $this->filterEqualOrIn('status', $value);
            }

            public function createdAt(string $value): Builder
            {
                return $this->filterTimestamp('created_at', $value);
            }

            public function customMethod(string $value): Builder
            {
                return $this->builder->where('custom_field', $value);
            }
        };
    }

    public function test_apply_calls_existing_methods_with_non_null_values(): void
    {
        $filter = $this->createFilter([
            'name' => 'John',
            'email' => 'john@example.com',
            'status' => 'active',
        ]);

        $result = $filter->apply($this->builder);

        $this->assertInstanceOf(Builder::class, $result);
        
        $sql = $result->toSql();
        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('email', $sql);
        $this->assertStringContainsString('status', $sql);
    }

    public function test_apply_skips_null_values(): void
    {
        $filter = $this->createFilter([
            'name' => 'John',
            'email' => null,
            'status' => '',
        ]);

        $result = $filter->apply($this->builder);
        
        $sql = $result->toSql();
        $this->assertStringContainsString('name', $sql);
        $this->assertStringNotContainsString('email', $sql);
        $this->assertStringContainsString('status', $sql); // Empty string is not null
    }

    public function test_apply_skips_non_existent_methods(): void
    {
        $filter = $this->createFilter([
            'name' => 'John',
            'nonExistentMethod' => 'value',
        ]);

        $result = $filter->apply($this->builder);
        
        $sql = $result->toSql();
        $this->assertStringContainsString('name', $sql);
        $this->assertStringNotContainsString('nonExistentMethod', $sql);
    }

    public function test_count_adds_with_count_for_single_relation(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder); // Initialize builder
        $result = $filter->count('roles');

        $sql = $result->toSql();
        $this->assertStringContainsString('roles_count', $sql);
    }

    public function test_count_adds_with_count_for_multiple_relations(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder); // Initialize builder
        $result = $filter->count('orgs,userGroups,requests');

        $sql = $result->toSql();
        $this->assertStringContainsString('orgs_count', $sql);
        $this->assertStringContainsString('user_groups_count', $sql);
        $this->assertStringContainsString('requests_count', $sql);
    }

    public function test_filter_calls_camel_cased_methods(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder); // Initialize builder
        $result = $filter->filter([
            'created_at' => '2023-01-01',
            'custom_method' => 'test_value',
        ]);

        $sql = $result->toSql();
        $this->assertStringContainsString('created_at', $sql);
        $this->assertStringContainsString('custom_field', $sql);
    }

    public function test_filter_skips_non_existent_methods(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder); // Initialize builder
        $result = $filter->filter([
            'name' => 'John',
            'non_existent_field' => 'value',
        ]);

        $sql = $result->toSql();
        $this->assertStringContainsString('name', $sql);
        $this->assertStringNotContainsString('non_existent_field', $sql);
    }

    public function test_sort_single_field_ascending(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder); // Initialize builder
        $result = $filter->sort('name');

        $sql = $result->toSql();
        $this->assertStringContainsString('order by `name` asc', strtolower($sql));
    }

    public function test_sort_single_field_descending(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder); // Initialize builder
        $result = $filter->sort('-name');

        $sql = $result->toSql();
        $this->assertStringContainsString('order by `name` desc', strtolower($sql));
    }

    public function test_sort_multiple_fields(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder); // Initialize builder
        $result = $filter->sort('name,-email,created_at');

        $sql = strtolower($result->toSql());
        $this->assertStringContainsString('order by `name` asc', $sql);
        $this->assertStringContainsString('`email` desc', $sql);
        $this->assertStringContainsString('`created_at` asc', $sql);
    }

    public function test_sort_skips_non_sortable_fields(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder); // Initialize builder
        $result = $filter->sort('name,password,secret');

        $sql = $result->toSql();
        $this->assertStringContainsString('name', $sql);
        $this->assertStringNotContainsString('password', $sql);
        $this->assertStringNotContainsString('secret', $sql);
    }

    public function test_include_single_relation(): void
    {
        $request = new Request();
        // Mock the isValidRelation method to return true
        $filter = new class($request) extends QueryFilter {
            protected function isValidRelation(string $relation): bool
            {
                return in_array($relation, ['orgs', 'userGroups']);
            }
        };
        
        $filter->apply($this->builder); // Initialize builder
        $result = $filter->include('orgs');
        
        // Check that the with method was called
        $this->assertArrayHasKey('orgs', $result->getEagerLoads());
    }

    public function test_include_multiple_relations(): void
    {
        $request = new Request();
        
        $filter = new class($request) extends QueryFilter {
            protected function isValidRelation(string $relation): bool
            {
                return in_array($relation, ['roles', 'permissions', 'userGroups']);
            }
        };
        
        $filter->apply($this->builder); // Initialize builder
        $result = $filter->include('roles,permissions,userGroups');
        
        $eagerLoads = $result->getEagerLoads();
        $this->assertArrayHasKey('roles', $eagerLoads);
        $this->assertArrayHasKey('permissions', $eagerLoads);
        $this->assertArrayHasKey('userGroups', $eagerLoads);
    }

    public function test_include_skips_invalid_relations(): void
    {
        $request = new Request();
        
        $filter = new class($request) extends QueryFilter {
            protected function isValidRelation(string $relation): bool
            {
                return $relation === 'orgs';
            }
        };
        
        $filter->apply($this->builder); // Initialize builder
        $result = $filter->include('orgs,invalidRelation');
        
        $eagerLoads = $result->getEagerLoads();
        $this->assertArrayHasKey('orgs', $eagerLoads);
        $this->assertArrayNotHasKey('invalidRelation', $eagerLoads);
    }

    public function test_filter_timestamp_with_range(): void
    {
        $filter = $this->createFilter();
        $result = $filter->apply($this->builder);
        
        // Use reflection to test protected method
        $reflection = new \ReflectionClass($filter);
        $method = $reflection->getMethod('filterTimestamp');
        $method->setAccessible(true);
        
        $result = $method->invoke($filter, 'created_at', '2023-01-01,2023-12-31');
        
        $sql = $result->toSql();
        $this->assertStringContainsString('between', strtolower($sql));
        $this->assertStringContainsString('created_at', $sql);
    }

    public function test_filter_timestamp_with_less_than(): void
    {
        $filter = $this->createFilter();
        $result = $filter->apply($this->builder);
        
        $reflection = new \ReflectionClass($filter);
        $method = $reflection->getMethod('filterTimestamp');
        $method->setAccessible(true);
        
        $result = $method->invoke($filter, 'created_at', '-2023-12-31');
        
        $sql = $result->toSql();
        $this->assertStringContainsString('<=', $sql);
        $this->assertStringContainsString('created_at', $sql);
    }

    public function test_filter_timestamp_with_greater_than(): void
    {
        $filter = $this->createFilter();
        $result = $filter->apply($this->builder);
        
        $reflection = new \ReflectionClass($filter);
        $method = $reflection->getMethod('filterTimestamp');
        $method->setAccessible(true);
        
        $result = $method->invoke($filter, 'created_at', '2023-01-01');
        
        $sql = $result->toSql();
        $this->assertStringContainsString('>=', $sql);
        $this->assertStringContainsString('created_at', $sql);
    }

    public function test_filter_like(): void
    {
        $filter = $this->createFilter();
        $result = $filter->apply($this->builder);
        
        $reflection = new \ReflectionClass($filter);
        $method = $reflection->getMethod('filterLike');
        $method->setAccessible(true);
        
        $result = $method->invoke($filter, 'name', 'John');
        
        $sql = $result->toSql();
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertStringContainsString('name', $sql);
    }

    public function test_filter_equal_or_in_single_value(): void
    {
        $filter = $this->createFilter();
        $result = $filter->apply($this->builder);
        
        $reflection = new \ReflectionClass($filter);
        $method = $reflection->getMethod('filterEqualOrIn');
        $method->setAccessible(true);
        
        $result = $method->invoke($filter, 'status', 'active');
        
        $sql = $result->toSql();
        $this->assertStringContainsString('status', $sql);
        $this->assertStringContainsString('=', $sql);
    }

    public function test_filter_equal_or_in_multiple_values(): void
    {
        $filter = $this->createFilter();
        $result = $filter->apply($this->builder);
        
        $reflection = new \ReflectionClass($filter);
        $method = $reflection->getMethod('filterEqualOrIn');
        $method->setAccessible(true);
        
        $result = $method->invoke($filter, 'status', 'active,inactive,pending');
        
        $sql = $result->toSql();
        $this->assertStringContainsString('status', $sql);
        $this->assertStringContainsString('in', strtolower($sql));
    }

    public function test_filter_equal(): void
    {
        $filter = $this->createFilter();
        $result = $filter->apply($this->builder);
        
        $reflection = new \ReflectionClass($filter);
        $method = $reflection->getMethod('filterEqual');
        $method->setAccessible(true);
        
        $result = $method->invoke($filter, 'email', 'john@example.com');
        
        $sql = $result->toSql();
        $this->assertStringContainsString('email', $sql);
        $this->assertStringContainsString('=', $sql);
    }

    public function test_is_valid_relation_returns_true_for_existing_method(): void
    {
        $filter = $this->createFilter();
        $result = $filter->apply($this->builder);
        
        $reflection = new \ReflectionClass($filter);
        $method = $reflection->getMethod('isValidRelation');
        $method->setAccessible(true);
        
        // Test with a relation that exists on User model
        $result = $method->invoke($filter, 'roles');
        $this->assertTrue($result);
    }

    public function test_is_valid_relation_returns_false_for_non_existent_method(): void
    {
        $filter = $this->createFilter();
        $result = $filter->apply($this->builder);
        
        $reflection = new \ReflectionClass($filter);
        $method = $reflection->getMethod('isValidRelation');
        $method->setAccessible(true);
        
        $result = $method->invoke($filter, 'nonExistentRelation');
        $this->assertFalse($result);
    }

    public function test_apply_with_empty_request(): void
    {
        $filter = $this->createFilter([]);
        $result = $filter->apply($this->builder);

        $this->assertInstanceOf(Builder::class, $result);
        $this->assertEquals($this->builder, $result);
    }

    public function test_apply_returns_builder_instance(): void
    {
        $filter = $this->createFilter(['name' => 'John']);
        $result = $filter->apply($this->builder);

        $this->assertInstanceOf(Builder::class, $result);
        $this->assertSame($this->builder, $result);
    }

    public function test_chaining_methods(): void
    {
        $filter = $this->createFilter();
        
        $result = $filter->apply($this->builder);
        $result = $filter->count('orgs');
        $result = $filter->sort('name,-created_at');

        $sql = $result->toSql();
        $this->assertStringContainsString('orgs_count', $sql);
        $this->assertStringContainsString('order by', strtolower($sql));
    }

    public function test_sort_with_empty_string(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder); // Initialize builder
        $result = $filter->sort('');

        // Should not add any order by clauses
        $sql = $result->toSql();
        $this->assertStringNotContainsString('order by', strtolower($sql));
    }

    public function test_count_with_empty_string(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder); // Initialize builder
        $result = $filter->count('');

        // Should still work but with empty relation
        $this->assertInstanceOf(Builder::class, $result);
    }

    public function test_include_with_empty_string(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder); // Initialize builder
        $result = $filter->include('');

        // Should not add any eager loads
        $eagerLoads = $result->getEagerLoads();
        $this->assertEmpty($eagerLoads);
    }

    public function test_filter_handles_boolean_values(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder); // Initialize builder
        $result = $filter->filter([
            'custom_method' => '1',
        ]);

        $sql = $result->toSql();
        $this->assertStringContainsString('custom_field', $sql);
    }

    public function test_complex_filter_combination(): void
    {
        $request = new Request([
            'name' => 'John',
            'status' => 'active,pending',
            'sort' => 'name,-created_at',
            'include' => 'orgs,userGroups',
            'count' => 'orgs,userGroups',
        ]);

        // Mock isValidRelation for include test
        $filter = new class($request) extends QueryFilter {
            protected array $sortable = ['name', 'email', 'created_at'];

            public function name(string $value): Builder
            {
                return $this->filterLike('name', $value);
            }

            public function status(string $value): Builder
            {
                return $this->filterEqualOrIn('status', $value);
            }

            protected function isValidRelation(string $relation): bool
            {
                return in_array($relation, ['orgs', 'userGroups']);
            }
        };

        $result = $filter->apply($this->builder);

        $sql = $result->toSql();
        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('status', $sql);
        $this->assertStringContainsString('orgs_count', $sql);
        $this->assertStringContainsString('user_groups_count', $sql);
        $this->assertStringContainsString('order by', strtolower($sql));

        $eagerLoads = $result->getEagerLoads();
        $this->assertArrayHasKey('orgs', $eagerLoads);
        $this->assertArrayHasKey('userGroups', $eagerLoads);
    }
}