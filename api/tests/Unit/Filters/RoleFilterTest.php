<?php

namespace Tests\Unit\Filters;

use App\Http\Filters\RoleFilter;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class RoleFilterTest extends TestCase
{
    use RefreshDatabase;

    private RoleFilter $filter;
    private Builder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = Role::query();
    }

    private function createFilter(array $params = []): RoleFilter
    {
        $request = new Request($params);
        return new RoleFilter($request);
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
        $result = $filter->name('Admin');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%Admin%', $bindings);
    }

    public function test_name_with_special_characters(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);
        $result = $filter->name("Super-Admin & User's Role");

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains("%Super-Admin & User's Role%", $bindings);
    }

    public function test_name_case_insensitive_search(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);
        $result = $filter->name('ADMINISTRATOR');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%ADMINISTRATOR%', $bindings);
    }

    public function test_description_uses_like_filter(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);
        $result = $filter->description('system administrator');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('description', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%system administrator%', $bindings);
    }

    public function test_description_partial_search(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);
        $result = $filter->description('full access');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('description', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%full access%', $bindings);
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
            'name' => 'Admin',
            'description' => 'system',
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
        $this->assertContains('%Admin%', $bindings);
        $this->assertContains('%system%', $bindings);
        $this->assertContains('2023-01-01', $bindings);
        $this->assertContains('2023-12-31', $bindings);
        $this->assertContains('2023-06-01', $bindings);
    }

    public function test_methods_return_builder_instance(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);

        $this->assertInstanceOf(Builder::class, $filter->name('Admin'));
        $this->assertInstanceOf(Builder::class, $filter->description('System Administrator'));
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
        $filter = $this->createFilter(['sort' => 'name,permissions,secret']);
        $result = $filter->apply($this->builder);

        $sql = $result->toSql();
        $this->assertStringContainsString('name', $sql);
        $this->assertStringNotContainsString('permissions', $sql);
        $this->assertStringNotContainsString('secret', $sql);
    }

    public function test_include_functionality(): void
    {
        $filter = $this->createFilter(['include' => 'permissions,users']);
        $result = $filter->apply($this->builder);

        $eagerLoads = $result->getEagerLoads();
        $this->assertArrayHasKey('permissions', $eagerLoads);
        $this->assertArrayHasKey('users', $eagerLoads);
    }

    public function test_count_functionality(): void
    {
        $filter = $this->createFilter(['count' => 'permissions,users']);
        $result = $filter->apply($this->builder);

        $sql = $result->toSql();
        $this->assertStringContainsString('permissions_count', $sql);
        $this->assertStringContainsString('users_count', $sql);
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
            'name' => 'Admin',
            'description' => null,
            'createdAt' => '2023-01-01',
        ]);

        $result = $filter->apply($this->builder);

        $sql = $result->toSql();
        $this->assertStringContainsString('name', $sql);
        $this->assertStringNotContainsString('description', $sql); // null values are skipped
        $this->assertStringContainsString('created_at', $sql);
    }

    public function test_complex_scenario_with_role_data(): void
    {
        // Create test roles with unique names
        Role::factory()->create([
            'name' => 'Test Administrator',
            'description' => 'System administrator with full access',
        ]);

        Role::factory()->create([
            'name' => 'Test User Role',
            'description' => 'Regular user with limited access',
        ]);

        Role::factory()->create([
            'name' => 'Test Moderator',
            'description' => 'Content moderator role',
        ]);

        $filter = $this->createFilter([
            'name' => 'Admin',
            'description' => 'system',
        ]);

        $result = $filter->apply(Role::query())->get();

        $this->assertCount(1, $result);
        $this->assertEquals('Test Administrator', $result->first()->name);
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

    public function test_name_search_with_unicode_characters(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);
        $result = $filter->name('Administrador');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%Administrador%', $bindings);
    }

    public function test_description_with_long_text(): void
    {
        $longDescription = 'This is a very long description that contains many words and explains the role in great detail including all the permissions and responsibilities that come with this particular role in the system';

        $filter = $this->createFilter();
        $filter->apply($this->builder);
        $result = $filter->description($longDescription);

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('description', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains("%{$longDescription}%", $bindings);
    }

    public function test_search_functionality_case_sensitivity(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder);

        // Test various case combinations
        $result1 = $filter->name('admin');
        $bindings1 = $result1->getBindings();
        $this->assertContains('%admin%', $bindings1);

        $result2 = $filter->name('ADMIN');
        $bindings2 = $result2->getBindings();
        $this->assertContains('%ADMIN%', $bindings2);

        $result3 = $filter->name('Admin');
        $bindings3 = $result3->getBindings();
        $this->assertContains('%Admin%', $bindings3);
    }

    public function test_role_filtering_real_world_scenario(): void
    {
        $filter = $this->createFilter([
            'name' => 'user',
            'description' => 'access',
            'createdAt' => '2023-01-01',
            'sort' => 'name,-created_at',
            'include' => 'permissions',
            'count' => 'users',
        ]);

        $result = $filter->apply($this->builder);

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        // Verify all components work together
        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('description', $sql);
        $this->assertStringContainsString('created_at', $sql);
        $this->assertStringContainsString('users_count', $sql);
        $this->assertStringContainsString('order by', strtolower($sql));

        $eagerLoads = $result->getEagerLoads();
        $this->assertArrayHasKey('permissions', $eagerLoads);

        $this->assertContains('%user%', $bindings);
        $this->assertContains('%access%', $bindings);
    }
}
