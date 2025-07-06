<?php

namespace Tests\Unit\Filters;

use App\Http\Filters\RequestFilter;
use App\Models\Request as RequestModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class RequestFilterTest extends TestCase
{
    use RefreshDatabase;

    private RequestFilter $filter;
    private Builder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = RequestModel::query();
    }

    private function createFilter(array $params = []): RequestFilter
    {
        $request = new Request($params);
        return new RequestFilter($request);
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
            'asset_id',
            'asset_account_id',
            'requester_id',
            'start_datetime',
            'end_datetime',
            'duration',
            'reason',
            'intended_query',
            'scope',
            'is_access_sensitive_data',
            'sensitive_data_note',
            'approver_note',
            'approver_risk_rating',
            'status',
            'approved_at',
            'rejected_at',
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

    public function test_asset_id_multiple_values(): void
    {
        $filter = $this->createFilter();
        $result = $filter->assetId('123,456,789');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('asset_id', $sql);
        $this->assertStringContainsString('in', strtolower($sql));
        $this->assertContains('123', $bindings);
        $this->assertContains('456', $bindings);
        $this->assertContains('789', $bindings);
    }

    public function test_asset_account_id_single_value(): void
    {
        $filter = $this->createFilter();
        $result = $filter->assetAccountId('456');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('asset_account_id', $sql);
        $this->assertStringContainsString('=', $sql);
        $this->assertContains('456', $bindings);
    }

    public function test_requester_id_multiple_values(): void
    {
        $filter = $this->createFilter();
        $result = $filter->requesterId('100,200,300');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('requester_id', $sql);
        $this->assertStringContainsString('in', strtolower($sql));
        $this->assertContains('100', $bindings);
        $this->assertContains('200', $bindings);
        $this->assertContains('300', $bindings);
    }

    public function test_start_datetime_range(): void
    {
        $filter = $this->createFilter();
        $result = $filter->startDatetime('2023-01-01,2023-12-31');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('start_datetime', $sql);
        $this->assertStringContainsString('between', strtolower($sql));
        $this->assertContains('2023-01-01', $bindings);
        $this->assertContains('2023-12-31', $bindings);
    }

    public function test_duration_greater_than(): void
    {
        $filter = $this->createFilter();
        $result = $filter->duration('60');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('duration', $sql);
        $this->assertStringContainsString('>=', $sql);
        $this->assertContains('60', $bindings);
    }

    public function test_reason_uses_like_filter(): void
    {
        $filter = $this->createFilter();
        $result = $filter->reason('emergency access');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('reason', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%emergency access%', $bindings);
    }

    public function test_intended_query_uses_like_filter(): void
    {
        $filter = $this->createFilter();
        $result = $filter->intendedQuery('SELECT * FROM users');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('intended_query', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%SELECT * FROM users%', $bindings);
    }

    public function test_scope_single_value(): void
    {
        $filter = $this->createFilter();
        $result = $filter->scope('read');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('scope', $sql);
        $this->assertStringContainsString('=', $sql);
        $this->assertContains('read', $bindings);
    }

    public function test_scope_multiple_values(): void
    {
        $filter = $this->createFilter();
        $result = $filter->scope('read,write,admin');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('scope', $sql);
        $this->assertStringContainsString('in', strtolower($sql));
        $this->assertContains('read', $bindings);
        $this->assertContains('write', $bindings);
        $this->assertContains('admin', $bindings);
    }

    public function test_is_access_sensitive_data_true(): void
    {
        $filter = $this->createFilter();
        $result = $filter->isAccessSensitiveData('1');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('is_access_sensitive_data', $sql);
        $this->assertStringContainsString('=', $sql);
        $this->assertContains('1', $bindings);
    }

    public function test_sensitive_data_note_uses_like_filter(): void
    {
        $filter = $this->createFilter();
        $result = $filter->sensitiveDataNote('customer PII');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('sensitive_data_note', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%customer PII%', $bindings);
    }

    public function test_approver_note_uses_like_filter(): void
    {
        $filter = $this->createFilter();
        $result = $filter->approverNote('approved for emergency');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('approver_note', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%approved for emergency%', $bindings);
    }

    public function test_approver_risk_rating_multiple_values(): void
    {
        $filter = $this->createFilter();
        $result = $filter->approverRiskRating('low,medium,high');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('approver_risk_rating', $sql);
        $this->assertStringContainsString('in', strtolower($sql));
        $this->assertContains('low', $bindings);
        $this->assertContains('medium', $bindings);
        $this->assertContains('high', $bindings);
    }

    public function test_status_multiple_values(): void
    {
        $filter = $this->createFilter();
        $result = $filter->status('pending,approved,rejected');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('status', $sql);
        $this->assertStringContainsString('in', strtolower($sql));
        $this->assertContains('pending', $bindings);
        $this->assertContains('approved', $bindings);
        $this->assertContains('rejected', $bindings);
    }

    public function test_approved_at_range(): void
    {
        $filter = $this->createFilter();
        $result = $filter->approvedAt('2023-01-01,2023-12-31');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('approved_at', $sql);
        $this->assertStringContainsString('between', strtolower($sql));
        $this->assertContains('2023-01-01', $bindings);
        $this->assertContains('2023-12-31', $bindings);
    }

    public function test_rejected_at_less_than(): void
    {
        $filter = $this->createFilter();
        $result = $filter->rejectedAt('-2023-12-31');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('rejected_at', $sql);
        $this->assertStringContainsString('<=', $sql);
        $this->assertContains('2023-12-31', $bindings);
    }

    public function test_apply_with_multiple_filters(): void
    {
        $filter = $this->createFilter([
            'orgId' => '123',
            'assetId' => '456,789',
            'requesterId' => '100',
            'reason' => 'emergency',
            'scope' => 'read,write',
            'status' => 'pending,approved',
            'isAccessSensitiveData' => '1',
        ]);

        $result = $filter->apply($this->builder);

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        // Check that all filters are applied
        $this->assertStringContainsString('org_id', $sql);
        $this->assertStringContainsString('asset_id', $sql);
        $this->assertStringContainsString('requester_id', $sql);
        $this->assertStringContainsString('reason', $sql);
        $this->assertStringContainsString('scope', $sql);
        $this->assertStringContainsString('status', $sql);
        $this->assertStringContainsString('is_access_sensitive_data', $sql);

        // Check bindings contain expected values
        $this->assertContains('123', $bindings);
        $this->assertContains('456', $bindings);
        $this->assertContains('789', $bindings);
        $this->assertContains('100', $bindings);
        $this->assertContains('%emergency%', $bindings);
        $this->assertContains('read', $bindings);
        $this->assertContains('write', $bindings);
        $this->assertContains('pending', $bindings);
        $this->assertContains('approved', $bindings);
        $this->assertContains('1', $bindings);
    }

    public function test_methods_return_builder_instance(): void
    {
        $filter = $this->createFilter();

        $this->assertInstanceOf(Builder::class, $filter->orgId('123'));
        $this->assertInstanceOf(Builder::class, $filter->assetId('456'));
        $this->assertInstanceOf(Builder::class, $filter->assetAccountId('789'));
        $this->assertInstanceOf(Builder::class, $filter->requesterId('100'));
        $this->assertInstanceOf(Builder::class, $filter->startDatetime('2023-01-01'));
        $this->assertInstanceOf(Builder::class, $filter->duration('60'));
        $this->assertInstanceOf(Builder::class, $filter->reason('emergency'));
        $this->assertInstanceOf(Builder::class, $filter->intendedQuery('SELECT'));
        $this->assertInstanceOf(Builder::class, $filter->scope('read'));
        $this->assertInstanceOf(Builder::class, $filter->isAccessSensitiveData('1'));
        $this->assertInstanceOf(Builder::class, $filter->sensitiveDataNote('note'));
        $this->assertInstanceOf(Builder::class, $filter->approverNote('note'));
        $this->assertInstanceOf(Builder::class, $filter->approverRiskRating('low'));
        $this->assertInstanceOf(Builder::class, $filter->status('pending'));
        $this->assertInstanceOf(Builder::class, $filter->approvedAt('2023-01-01'));
        $this->assertInstanceOf(Builder::class, $filter->rejectedAt('2023-01-01'));
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
        $filter = $this->createFilter(['sort' => 'status,-created_at,requester_id']);
        $result = $filter->apply($this->builder);

        $sql = strtolower($result->toSql());
        $this->assertStringContainsString('order by "status" asc', $sql);
        $this->assertStringContainsString('"created_at" desc', $sql);
        $this->assertStringContainsString('"requester_id" asc', $sql);
    }
}