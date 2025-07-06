<?php

namespace Tests\Unit\Filters;

use App\Http\Filters\UserFilter;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class UserFilterTest extends TestCase
{
    use RefreshDatabase;

    private UserFilter $filter;
    private Builder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = User::query();
    }

    private function createFilter(array $params = []): UserFilter
    {
        $request = new Request($params);
        return new UserFilter($request);
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
            'email',
            'status',
            'last_login_at',
            'created_at',
            'updated_at',
        ];

        $this->assertEquals($expectedSortable, $sortable);
    }

    public function test_org_id_single_value(): void
    {
        $filter = $this->createFilter();
        $result = $filter->orgId('123');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('org_id', $sql);
        $this->assertStringContainsString('=', $sql);
        $this->assertContains('123', $bindings);
    }

    public function test_org_id_multiple_values(): void
    {
        $filter = $this->createFilter();
        $result = $filter->orgId('123,456,789');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('org_id', $sql);
        $this->assertStringContainsString('in', strtolower($sql));
        $this->assertContains('123', $bindings);
        $this->assertContains('456', $bindings);
        $this->assertContains('789', $bindings);
    }

    public function test_name_uses_like_filter(): void
    {
        $filter = $this->createFilter();
        $result = $filter->name('John');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%John%', $bindings);
    }

    public function test_name_with_special_characters(): void
    {
        $filter = $this->createFilter();
        $result = $filter->name("O'Connor");

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains("%O'Connor%", $bindings);
    }

    public function test_email_uses_like_filter(): void
    {
        $filter = $this->createFilter();
        $result = $filter->email('john@example.com');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('email', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%john@example.com%', $bindings);
    }

    public function test_email_partial_search(): void
    {
        $filter = $this->createFilter();
        $result = $filter->email('gmail');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('email', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%gmail%', $bindings);
    }

    public function test_status_single_value(): void
    {
        $filter = $this->createFilter();
        $result = $filter->status('active');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('status', $sql);
        $this->assertStringContainsString('=', $sql);
        $this->assertContains('active', $bindings);
    }

    public function test_status_multiple_values(): void
    {
        $filter = $this->createFilter();
        $result = $filter->status('active,inactive,pending');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('status', $sql);
        $this->assertStringContainsString('in', strtolower($sql));
        $this->assertContains('active', $bindings);
        $this->assertContains('inactive', $bindings);
        $this->assertContains('pending', $bindings);
    }

    public function test_created_at_range(): void
    {
        $filter = $this->createFilter();
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
        $result = $filter->createdAt('2023-01-01');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('created_at', $sql);
        $this->assertStringContainsString('>=', $sql);
        $this->assertContains('2023-01-01', $bindings);
    }

    public function test_created_at_less_than(): void
    {
        $filter = $this->createFilter();
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
        $result = $filter->updatedAt('2023-06-01,2023-06-30');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('updated_at', $sql);
        $this->assertStringContainsString('between', strtolower($sql));
        $this->assertContains('2023-06-01', $bindings);
        $this->assertContains('2023-06-30', $bindings);
    }

    public function test_last_login_at_range(): void
    {
        $filter = $this->createFilter();
        $result = $filter->lastLoginAt('2023-11-01,2023-11-30');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('last_login_at', $sql);
        $this->assertStringContainsString('between', strtolower($sql));
        $this->assertContains('2023-11-01', $bindings);
        $this->assertContains('2023-11-30', $bindings);
    }

    public function test_last_login_at_greater_than(): void
    {
        $filter = $this->createFilter();
        $result = $filter->lastLoginAt('2023-11-01');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('last_login_at', $sql);
        $this->assertStringContainsString('>=', $sql);
        $this->assertContains('2023-11-01', $bindings);
    }

    public function test_two_factor_enabled_true(): void
    {
        $filter = $this->createFilter();
        $result = $filter->twoFactorEnabled('1');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('two_factor_enabled', $sql);
        $this->assertStringContainsString('=', $sql);
        $this->assertContains('1', $bindings);
    }

    public function test_two_factor_enabled_false(): void
    {
        $filter = $this->createFilter();
        $result = $filter->twoFactorEnabled('0');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('two_factor_enabled', $sql);
        $this->assertStringContainsString('=', $sql);
        $this->assertContains('0', $bindings);
    }

    public function test_apply_with_all_filters(): void
    {
        $filter = $this->createFilter([
            'orgId' => '123',
            'name' => 'John',
            'email' => 'example.com',
            'status' => 'active,pending',
            'createdAt' => '2023-01-01,2023-12-31',
            'updatedAt' => '2023-06-01',
            'lastLoginAt' => '-2023-11-30',
            'twoFactorEnabled' => '1',
        ]);

        $result = $filter->apply($this->builder);

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        // Check that all filters are applied
        $this->assertStringContainsString('org_id', $sql);
        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('email', $sql);
        $this->assertStringContainsString('status', $sql);
        $this->assertStringContainsString('created_at', $sql);
        $this->assertStringContainsString('updated_at', $sql);
        $this->assertStringContainsString('last_login_at', $sql);
        $this->assertStringContainsString('two_factor_enabled', $sql);

        // Check bindings contain expected values
        $this->assertContains('123', $bindings);
        $this->assertContains('%John%', $bindings);
        $this->assertContains('%example.com%', $bindings);
        $this->assertContains('active', $bindings);
        $this->assertContains('pending', $bindings);
        $this->assertContains('1', $bindings);
    }

    public function test_sort_functionality(): void
    {
        $filter = $this->createFilter(['sort' => 'name,-email,created_at']);
        $result = $filter->apply($this->builder);

        $sql = strtolower($result->toSql());
        $this->assertStringContainsString('order by "name" asc', $sql);
        $this->assertStringContainsString('"email" desc', $sql);
        $this->assertStringContainsString('"created_at" asc', $sql);
    }

    public function test_sort_with_non_sortable_field(): void
    {
        $filter = $this->createFilter(['sort' => 'name,password,secret']);
        $result = $filter->apply($this->builder);

        $sql = $result->toSql();
        $this->assertStringContainsString('name', $sql);
        $this->assertStringNotContainsString('password', $sql);
        $this->assertStringNotContainsString('secret', $sql);
    }

    public function test_include_functionality(): void
    {
        $filter = $this->createFilter(['include' => 'roles,permissions']);
        $result = $filter->apply($this->builder);

        $eagerLoads = $result->getEagerLoads();
        $this->assertArrayHasKey('roles', $eagerLoads);
        $this->assertArrayHasKey('permissions', $eagerLoads);
    }

    public function test_count_functionality(): void
    {
        $filter = $this->createFilter(['count' => 'roles,permissions']);
        $result = $filter->apply($this->builder);

        $sql = $result->toSql();
        $this->assertStringContainsString('roles_count', $sql);
        $this->assertStringContainsString('permissions_count', $sql);
    }

    public function test_filter_with_empty_values(): void
    {
        $filter = $this->createFilter([
            'name' => '',
            'email' => '',
            'status' => '',
        ]);

        $result = $filter->apply($this->builder);

        $sql = $result->toSql();
        // Empty strings should still be processed
        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('email', $sql);
        $this->assertStringContainsString('status', $sql);
    }

    public function test_filter_with_null_values(): void
    {
        $filter = $this->createFilter([
            'name' => 'John',
            'email' => null,
            'status' => 'active',
        ]);

        $result = $filter->apply($this->builder);

        $sql = $result->toSql();
        $this->assertStringContainsString('name', $sql);
        $this->assertStringNotContainsString('email', $sql); // null values are skipped
        $this->assertStringContainsString('status', $sql);
    }

    public function test_complex_scenario_with_user_data(): void
    {
        // Create test users
        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'status' => 'active',
        ]);

        User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@gmail.com',
            'status' => 'inactive',
        ]);

        $filter = $this->createFilter([
            'name' => 'John',
            'status' => 'active',
        ]);

        $result = $filter->apply(User::query())->get();

        $this->assertCount(1, $result);
        $this->assertEquals('John Doe', $result->first()->name);
    }

    public function test_timestamp_filters_with_datetime_strings(): void
    {
        $filter = $this->createFilter();
        
        $result = $filter->createdAt('2023-01-01 00:00:00,2023-12-31 23:59:59');
        
        $sql = $result->toSql();
        $bindings = $result->getBindings();
        
        $this->assertStringContainsString('created_at', $sql);
        $this->assertStringContainsString('between', strtolower($sql));
        $this->assertContains('2023-01-01 00:00:00', $bindings);
        $this->assertContains('2023-12-31 23:59:59', $bindings);
    }

    public function test_org_id_with_single_zero_value(): void
    {
        $filter = $this->createFilter();
        $result = $filter->orgId('0');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('org_id', $sql);
        $this->assertStringContainsString('=', $sql);
        $this->assertContains('0', $bindings);
    }

    public function test_methods_return_builder_instance(): void
    {
        $filter = $this->createFilter();

        $this->assertInstanceOf(Builder::class, $filter->orgId('123'));
        $this->assertInstanceOf(Builder::class, $filter->name('John'));
        $this->assertInstanceOf(Builder::class, $filter->email('test@example.com'));
        $this->assertInstanceOf(Builder::class, $filter->status('active'));
        $this->assertInstanceOf(Builder::class, $filter->createdAt('2023-01-01'));
        $this->assertInstanceOf(Builder::class, $filter->updatedAt('2023-01-01'));
        $this->assertInstanceOf(Builder::class, $filter->lastLoginAt('2023-01-01'));
        $this->assertInstanceOf(Builder::class, $filter->twoFactorEnabled('1'));
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

    public function test_inheritance_from_query_filter(): void
    {
        $filter = $this->createFilter();
        
        $this->assertInstanceOf(\App\Http\Filters\QueryFilter::class, $filter);
    }

    public function test_case_insensitive_email_search(): void
    {
        $filter = $this->createFilter();
        $result = $filter->email('JOHN@EXAMPLE.COM');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('email', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%JOHN@EXAMPLE.COM%', $bindings);
    }

    public function test_name_search_with_unicode_characters(): void
    {
        $filter = $this->createFilter();
        $result = $filter->name('José');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%José%', $bindings);
    }

    public function test_status_with_mixed_case(): void
    {
        $filter = $this->createFilter();
        $result = $filter->status('ACTIVE,Inactive,Pending');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('status', $sql);
        $this->assertStringContainsString('in', strtolower($sql));
        $this->assertContains('ACTIVE', $bindings);
        $this->assertContains('Inactive', $bindings);
        $this->assertContains('Pending', $bindings);
    }
}