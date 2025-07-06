<?php

namespace Tests\Unit\Filters;

use App\Http\Filters\AssetFilter;
use App\Models\Asset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AssetFilterTest extends TestCase
{
    use RefreshDatabase;

    private AssetFilter $filter;
    private Builder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = Asset::query();
    }

    private function createFilter(array $params = []): AssetFilter
    {
        $request = new Request($params);
        return new AssetFilter($request);
    }

    public function test_sortable_fields_are_defined(): void
    {
        $filter = $this->createFilter();
        
        $reflection = new \ReflectionClass($filter);
        $property = $reflection->getProperty('sortable');
        $property->setAccessible(true);
        $sortable = $property->getValue($filter);

        $expectedSortable = [
            'org_id',
            'name',
            'description',
            'status',
            'host',
            'port',
            'dbms',
            'created_at',
            'updated_at',
        ];

        $this->assertEquals($expectedSortable, $sortable);
    }

    public function test_org_id_single_value(): void
    {
        $filter = $this->createFilter();
        $filter->apply($this->builder); // Initialize builder
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
        $result = $filter->name('Database');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%Database%', $bindings);
    }

    public function test_description_uses_like_filter(): void
    {
        $filter = $this->createFilter();
        $result = $filter->description('production database');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('description', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%production database%', $bindings);
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
        $result = $filter->status('active,inactive,maintenance');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('status', $sql);
        $this->assertStringContainsString('in', strtolower($sql));
        $this->assertContains('active', $bindings);
        $this->assertContains('inactive', $bindings);
        $this->assertContains('maintenance', $bindings);
    }

    public function test_host_uses_like_filter(): void
    {
        $filter = $this->createFilter();
        $result = $filter->host('localhost');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('host', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%localhost%', $bindings);
    }

    public function test_port_uses_equal_filter(): void
    {
        $filter = $this->createFilter();
        $result = $filter->port('5432');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('port', $sql);
        $this->assertStringContainsString('=', $sql);
        $this->assertContains('5432', $bindings);
    }

    public function test_dbms_single_value(): void
    {
        $filter = $this->createFilter();
        $result = $filter->dbms('postgresql');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('dbms', $sql);
        $this->assertStringContainsString('=', $sql);
        $this->assertContains('postgresql', $bindings);
    }

    public function test_dbms_multiple_values(): void
    {
        $filter = $this->createFilter();
        $result = $filter->dbms('postgresql,mysql,oracle');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('dbms', $sql);
        $this->assertStringContainsString('in', strtolower($sql));
        $this->assertContains('postgresql', $bindings);
        $this->assertContains('mysql', $bindings);
        $this->assertContains('oracle', $bindings);
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

    public function test_updated_at_greater_than(): void
    {
        $filter = $this->createFilter();
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
            'orgId' => '123',
            'name' => 'Database',
            'description' => 'production',
            'status' => 'active,maintenance',
            'host' => 'localhost',
            'port' => '5432',
            'dbms' => 'postgresql,mysql',
            'createdAt' => '2023-01-01,2023-12-31',
            'updatedAt' => '2023-06-01',
        ]);

        $result = $filter->apply($this->builder);

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        // Check that all filters are applied
        $this->assertStringContainsString('org_id', $sql);
        $this->assertStringContainsString('name', $sql);
        $this->assertStringContainsString('description', $sql);
        $this->assertStringContainsString('status', $sql);
        $this->assertStringContainsString('host', $sql);
        $this->assertStringContainsString('port', $sql);
        $this->assertStringContainsString('dbms', $sql);
        $this->assertStringContainsString('created_at', $sql);
        $this->assertStringContainsString('updated_at', $sql);

        // Check bindings contain expected values
        $this->assertContains('123', $bindings);
        $this->assertContains('%Database%', $bindings);
        $this->assertContains('%production%', $bindings);
        $this->assertContains('active', $bindings);
        $this->assertContains('maintenance', $bindings);
        $this->assertContains('%localhost%', $bindings);
        $this->assertContains('5432', $bindings);
        $this->assertContains('postgresql', $bindings);
        $this->assertContains('mysql', $bindings);
    }

    public function test_methods_return_builder_instance(): void
    {
        $filter = $this->createFilter();

        $this->assertInstanceOf(Builder::class, $filter->orgId('123'));
        $this->assertInstanceOf(Builder::class, $filter->name('Database'));
        $this->assertInstanceOf(Builder::class, $filter->description('Production'));
        $this->assertInstanceOf(Builder::class, $filter->status('active'));
        $this->assertInstanceOf(Builder::class, $filter->host('localhost'));
        $this->assertInstanceOf(Builder::class, $filter->port('5432'));
        $this->assertInstanceOf(Builder::class, $filter->dbms('postgresql'));
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
        $this->assertStringContainsString('order by "name" asc', $sql);
        $this->assertStringContainsString('"status" desc', $sql);
        $this->assertStringContainsString('"created_at" asc', $sql);
    }

    public function test_port_with_different_values(): void
    {
        $filter = $this->createFilter();
        
        // Test common database ports
        $commonPorts = ['3306', '5432', '1521', '1433'];
        
        foreach ($commonPorts as $port) {
            $result = $filter->port($port);
            $bindings = $result->getBindings();
            $this->assertContains($port, $bindings);
        }
    }

    public function test_host_with_ip_addresses(): void
    {
        $filter = $this->createFilter();
        $result = $filter->host('192.168.1.100');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('host', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%192.168.1.100%', $bindings);
    }

    public function test_dbms_case_insensitive(): void
    {
        $filter = $this->createFilter();
        $result = $filter->dbms('PostgreSQL,MySQL,Oracle');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('dbms', $sql);
        $this->assertContains('PostgreSQL', $bindings);
        $this->assertContains('MySQL', $bindings);
        $this->assertContains('Oracle', $bindings);
    }
}