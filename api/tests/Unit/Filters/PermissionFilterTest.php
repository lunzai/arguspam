<?php

namespace Tests\Unit\Filters;

use App\Http\Filters\PermissionFilter;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class PermissionFilterTest extends TestCase
{
    use RefreshDatabase;

    private PermissionFilter $filter;
    private Builder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = Permission::query();
    }

    private function createFilter(array $params = []): PermissionFilter
    {
        $request = new Request($params);
        return new PermissionFilter($request);
    }

    public function test_sortable_fields_are_defined(): void
    {
        $filter = $this->createFilter();
        
        $reflection = new \ReflectionClass($filter);
        $property = $reflection->getProperty('sortable');
        $property->setAccessible(true);
        $sortable = $property->getValue($filter);

        $expectedSortable = [
            'name',
            'description',
            'created_at',
            'updated_at',
        ];

        $this->assertEquals($expectedSortable, $sortable);
    }

    public function test_name_uses_like_filter(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);
        $result = $filter->name('user:create');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%user:create%', $bindings);
    }

    public function test_name_with_permission_patterns(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);
        $result = $filter->name('asset:view');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%asset:view%', $bindings);
    }

    public function test_name_partial_search(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);
        $result = $filter->name('create');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%create%', $bindings);
    }

    public function test_name_case_insensitive_search(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);
        $result = $filter->name('USER:CREATE');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%USER:CREATE%', $bindings);
    }

    public function test_description_uses_like_filter(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);
        $result = $filter->description('create user accounts');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('description', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%create user accounts%', $bindings);
    }

    public function test_description_partial_search(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);
        $result = $filter->description('permission');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('description', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%permission%', $bindings);
    }

    public function test_created_at_range(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);
        $result = $filter->createdAt('2023-01-01,2023-12-31');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('created_at', $sql);
        $this->assertStringContainsString('between', strtolower($sql));
        $this->assertContains('2023-01-01', $bindings);
        $this->assertContains('2023-12-31', $bindings);
    }

    public function test_created_at_greater_than(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);
        $result = $filter->createdAt('2023-06-01');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('created_at', $sql);
        $this->assertStringContainsString('>=', $sql);
        $this->assertContains('2023-06-01', $bindings);
    }

    public function test_created_at_less_than(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);
        $result = $filter->createdAt('-2023-12-31');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('created_at', $sql);
        $this->assertStringContainsString('<=', $sql);
        $this->assertContains('2023-12-31', $bindings);
    }

    public function test_updated_at_range(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);
        $result = $filter->updatedAt('2023-06-01,2023-06-30');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('updated_at', $sql);
        $this->assertStringContainsString('between', strtolower($sql));
        $this->assertContains('2023-06-01', $bindings);
        $this->assertContains('2023-06-30', $bindings);
    }

    public function test_updated_at_greater_than(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);
        $result = $filter->updatedAt('2023-11-01');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('updated_at', $sql);
        $this->assertStringContainsString('>=', $sql);
        $this->assertContains('2023-11-01', $bindings);
    }

    public function test_apply_with_all_filters(): void
    {
        $filter = $this->createFilter([
            'name' => 'user:create',
            'description' => 'create permission',
            'createdAt' => '2023-01-01,2023-12-31',
            'updatedAt' => '2023-06-01',
        ]);

        $result = $filter->apply($this->builder);

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        // Check that all filters are applied
        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('description', $sql);
        $this->assertStringContainsString('created_at', $sql);
        $this->assertStringContainsString('updated_at', $sql);

        // Check bindings contain expected values
        $this->assertContains('%user:create%', $bindings);
        $this->assertContains('%create permission%', $bindings);
        $this->assertContains('2023-01-01', $bindings);
        $this->assertContains('2023-12-31', $bindings);
        $this->assertContains('2023-06-01', $bindings);
    }

    public function test_methods_return_builder_instance(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);

        $this->assertInstanceOf(Builder::class, $filter->name('user:create'));
        $this->assertInstanceOf(Builder::class, $filter->description('Create users'));
        $this->assertInstanceOf(Builder::class, $filter->createdAt('2023-01-01'));
        $this->assertInstanceOf(Builder::class, $filter->updatedAt('2023-01-01'));
    }

    public function test_inheritance_from_query_filter(): void
    {
        $filter = $this->createFilter();
        
        $this->assertInstanceOf(\App\Http\Filters\QueryFilter::class, $filter);
    }

    public function test_sort_functionality(): void
    {
        $filter = $this->createFilter(['sort' => 'name,-created_at,description']);
        $result = $filter->apply($this->builder);

        $sql = strtolower($result->toSql());
        $this->assertStringContainsString('order by `name` asc', $sql);
        $this->assertStringContainsString('`created_at` desc', $sql);
        $this->assertStringContainsString('`description` asc', $sql);
    }

    public function test_sort_with_non_sortable_field(): void
    {
        $filter = $this->createFilter(['sort' => 'name,roles,secret']);
        $result = $filter->apply($this->builder);

        $sql = $result->toSql();
        $this->assertStringContainsString('name', $sql);
        $this->assertStringNotContainsString('roles', $sql);
        $this->assertStringNotContainsString('secret', $sql);
    }

    public function test_include_functionality(): void
    {
        $filter = $this->createFilter(['include' => 'roles']);
        $result = $filter->apply($this->builder);

        $eagerLoads = $result->getEagerLoads();
        $this->assertArrayHasKey('roles', $eagerLoads);
    }

    public function test_count_functionality(): void
    {
        $filter = $this->createFilter(['count' => 'roles']);
        $result = $filter->apply($this->builder);

        $sql = $result->toSql();
        $this->assertStringContainsString('roles_count', $sql);
    }

    public function test_filter_with_empty_values(): void
    {
        $filter = $this->createFilter([
            'name' => '',
            'description' => '',
        ]);

        $result = $filter->apply($this->builder);

        $sql = $result->toSql();
        // Empty strings should still be processed
        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('description', $sql);
    }

    public function test_filter_with_null_values(): void
    {
        $filter = $this->createFilter([
            'name' => 'user:create',
            'description' => null,
            'createdAt' => '2023-01-01',
        ]);

        $result = $filter->apply($this->builder);

        $sql = $result->toSql();
        $this->assertStringContainsString('name', $sql);
        $this->assertStringNotContainsString('description', $sql); // null values are skipped
        $this->assertStringContainsString('created_at', $sql);
    }

    public function test_complex_scenario_with_permission_data(): void
    {
        // Create test permissions with unique names
        Permission::factory()->create([
            'name' => 'test:user:create',
            'description' => 'Create user accounts',
        ]);

        Permission::factory()->create([
            'name' => 'test:user:view',
            'description' => 'View user accounts',
        ]);

        Permission::factory()->create([
            'name' => 'test:asset:delete',
            'description' => 'Delete assets',
        ]);

        $filter = $this->createFilter([
            'name' => 'user',
            'description' => 'user accounts',
        ]);

        $result = $filter->apply(Permission::query())->get();

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains('name', 'test:user:create'));
        $this->assertTrue($result->contains('name', 'test:user:view'));
    }

    public function test_timestamp_filters_with_datetime_strings(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);
        
        $result = $filter->createdAt('2023-01-01 00:00:00,2023-12-31 23:59:59');
        
        $sql = $result->toSql();
        $bindings = $result->getBindings();
        
        $this->assertStringContainsString('created_at', $sql);
        $this->assertStringContainsString('between', strtolower($sql));
        $this->assertContains('2023-01-01 00:00:00', $bindings);
        $this->assertContains('2023-12-31 23:59:59', $bindings);
    }

    public function test_chaining_filter_methods(): void
    {
        $filter = $this->createFilter();

        $result = $filter->apply($this->builder)
            ->where('id', '>', 0);

        $this->assertInstanceOf(Builder::class, $result);
        
        $sql = $result->toSql();
        $this->assertStringContainsString('id', $sql);
        $this->assertStringContainsString('>', $sql);
    }

    public function test_permission_name_patterns(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);

        // Test various permission naming patterns
        $patterns = [
            'user:create',
            'asset:view',
            'session:delete',
            'org:manage',
            'permission:assign',
            'role:update',
        ];

        foreach ($patterns as $pattern) {
            $result = $filter->name($pattern);
            $bindings = $result->getBindings();
            $this->assertContains("%{$pattern}%", $bindings);
        }
    }

    public function test_description_with_special_characters(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);
        $result = $filter->description("Create & manage user's permissions");

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('description', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains("%Create & manage user's permissions%", $bindings);
    }

    public function test_search_functionality_case_sensitivity(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);

        // Test various case combinations for permission names
        $result1 = $filter->name('user:create');
        $bindings1 = $result1->getBindings();
        $this->assertContains('%user:create%', $bindings1);

        $result2 = $filter->name('USER:CREATE');
        $bindings2 = $result2->getBindings();
        $this->assertContains('%USER:CREATE%', $bindings2);

        $result3 = $filter->name('User:Create');
        $bindings3 = $result3->getBindings();
        $this->assertContains('%User:Create%', $bindings3);
    }

    public function test_permission_filtering_real_world_scenario(): void
    {
        $filter = $this->createFilter([
            'name' => 'user',
            'description' => 'permission',
            'createdAt' => '2023-01-01',
            'sort' => 'name,-created_at',
            'include' => 'roles',
            'count' => 'roles',
        ]);

        $result = $filter->apply($this->builder);

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        // Verify all components work together
        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('description', $sql);
        $this->assertStringContainsString('created_at', $sql);
        $this->assertStringContainsString('roles_count', $sql);
        $this->assertStringContainsString('order by', strtolower($sql));

        $eagerLoads = $result->getEagerLoads();
        $this->assertArrayHasKey('roles', $eagerLoads);

        $this->assertContains('%user%', $bindings);
        $this->assertContains('%permission%', $bindings);
    }

    public function test_permission_name_with_wildcards(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);
        $result = $filter->name('*:create');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%*:create%', $bindings);
    }

    public function test_description_with_long_text(): void
    {
        $longDescription = 'This permission allows users to create new user accounts in the system with all necessary validations and security checks including email verification and password requirements';
        
        $filter = $this->createFilter();
        $filter->apply($this->builder);
        $result = $filter->description($longDescription);

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('description', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains("%{$longDescription}%", $bindings);
    }

    public function test_resource_based_permission_filtering(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);

        // Test filtering by different resource types
        $resources = ['user', 'asset', 'session', 'org', 'role', 'permission'];
        
        foreach ($resources as $resource) {
            $result = $filter->name($resource);
            $bindings = $result->getBindings();
            $this->assertContains("%{$resource}%", $bindings);
        }
    }

    public function test_action_based_permission_filtering(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);

        // Test filtering by different actions
        $actions = ['create', 'view', 'update', 'delete', 'manage', 'assign'];
        
        foreach ($actions as $action) {
            $result = $filter->name($action);
            $bindings = $result->getBindings();
            $this->assertContains("%{$action}%", $bindings);
        }
    }
}