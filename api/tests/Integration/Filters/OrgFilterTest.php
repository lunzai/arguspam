<?php

namespace Tests\Integration\Filters;

use App\Http\Filters\OrgFilter;
use App\Models\Org;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class OrgFilterTest extends TestCase
{
    use RefreshDatabase;

    private OrgFilter $filter;
    private Builder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = Org::query();
    }

    private function createFilter(array $params = []): OrgFilter
    {
        $request = new Request($params);
        return new OrgFilter($request);
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
            'status',
            'created_at',
            'updated_at',
        ];

        $this->assertEquals($expectedSortable, $sortable);
    }

    public function test_name_uses_like_filter(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder); // Initialize builder
        $result = $filter->name('Acme Corp');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%Acme Corp%', $bindings);
    }

    public function test_description_uses_like_filter(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder); // Initialize builder
        $result = $filter->description('technology company');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('description', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%technology company%', $bindings);
    }

    public function test_status_single_value(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder); // Initialize builder
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
        $filter->apply($this->builder); // Initialize builder
        $result = $filter->status('active,inactive,suspended');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('status', $sql);
        $this->assertStringContainsString('in', strtolower($sql));
        $this->assertContains('active', $bindings);
        $this->assertContains('inactive', $bindings);
        $this->assertContains('suspended', $bindings);
    }

    public function test_created_at_range(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder); // Initialize builder
        $result = $filter->createdAt('2023-01-01,2023-12-31');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('created_at', $sql);
        $this->assertStringContainsString('between', strtolower($sql));
        $this->assertContains('2023-01-01', $bindings);
        $this->assertContains('2023-12-31', $bindings);
    }

    public function test_updated_at_greater_than(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder); // Initialize builder
        $result = $filter->updatedAt('2023-06-01');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('updated_at', $sql);
        $this->assertStringContainsString('>=', $sql);
        $this->assertContains('2023-06-01', $bindings);
    }

    public function test_apply_with_all_filters(): void
    {
        $filter = $this->createFilter([
            'name' => 'Acme',
            'description' => 'technology',
            'status' => 'active,pending',
            'createdAt' => '2023-01-01,2023-12-31',
            'updatedAt' => '2023-06-01',
        ]);

        $result = $filter->apply($this->builder);

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        // Check that all filters are applied
        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('description', $sql);
        $this->assertStringContainsString('status', $sql);
        $this->assertStringContainsString('created_at', $sql);
        $this->assertStringContainsString('updated_at', $sql);

        // Check bindings contain expected values
        $this->assertContains('%Acme%', $bindings);
        $this->assertContains('%technology%', $bindings);
        $this->assertContains('active', $bindings);
        $this->assertContains('pending', $bindings);
    }

    public function test_methods_return_builder_instance(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder); // Initialize builder

        $this->assertInstanceOf(Builder::class, $filter->name('Acme'));
        $this->assertInstanceOf(Builder::class, $filter->description('Technology'));
        $this->assertInstanceOf(Builder::class, $filter->status('active'));
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
        $filter = $this->createFilter(['sort' => 'name,-status,created_at']);
        $result = $filter->apply($this->builder);

        $sql = strtolower($result->toSql());
        $this->assertStringContainsString('order by `name` asc', $sql);
        $this->assertStringContainsString('`status` desc', $sql);
        $this->assertStringContainsString('`created_at` asc', $sql);
    }
}
