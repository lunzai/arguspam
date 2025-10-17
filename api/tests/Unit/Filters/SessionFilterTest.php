<?php

namespace Tests\Unit\Filters;

use App\Http\Filters\SessionFilter;
use App\Models\Session;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Tests\TestCase;

class SessionFilterTest extends TestCase
{
    private SessionFilter $filter;
    private Builder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = Session::query();
    }

    private function createFilter(array $params = []): SessionFilter
    {
        $request = new Request($params);
        $filter = new SessionFilter($request);
        $filter->apply($this->builder); // Initialize builder once
        return $filter;
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
            'request_id',
            'asset_id',
            'requester_id',
            'approver_id',
            'start_datetime',
            'end_datetime',
            'scheduled_end_datetime',
            'requested_duration',
            'actual_duration',
            'is_jit',
            'account_name',
            'is_expired',
            'is_terminated',
            'is_checkin',
            'status',
            'checkin_at',
            'terminated_at',
            'ended_at',
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

    public function test_request_id_single_value(): void
    {
        $filter = $this->createFilter();
        $result = $filter->requestId('456');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('request_id', $sql);
        $this->assertStringContainsString('=', $sql);
        $this->assertContains('456', $bindings);
    }

    public function test_asset_id_multiple_values(): void
    {
        $filter = $this->createFilter();
        $result = $filter->assetId('111,222,333');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('asset_id', $sql);
        $this->assertStringContainsString('in', strtolower($sql));
        $this->assertContains('111', $bindings);
        $this->assertContains('222', $bindings);
        $this->assertContains('333', $bindings);
    }

    public function test_requester_id_filtering(): void
    {
        $filter = $this->createFilter();
        $result = $filter->requesterId('100,200');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('requester_id', $sql);
        $this->assertStringContainsString('in', strtolower($sql));
        $this->assertContains('100', $bindings);
        $this->assertContains('200', $bindings);
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

    public function test_end_datetime_greater_than(): void
    {
        $filter = $this->createFilter();
        $result = $filter->endDatetime('2023-06-01');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('end_datetime', $sql);
        $this->assertStringContainsString('>=', $sql);
        $this->assertContains('2023-06-01', $bindings);
    }

    public function test_scheduled_end_datetime_less_than(): void
    {
        $filter = $this->createFilter();
        $result = $filter->scheduledEndDatetime('-2023-12-31');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('scheduled_end_datetime', $sql);
        $this->assertStringContainsString('<=', $sql);
        $this->assertContains('2023-12-31', $bindings);
    }

    public function test_requested_duration_filtering(): void
    {
        $filter = $this->createFilter();
        $result = $filter->requestedDuration('60,120');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('requested_duration', $sql);
        $this->assertStringContainsString('between', strtolower($sql));
        $this->assertContains('60', $bindings);
        $this->assertContains('120', $bindings);
    }

    public function test_actual_duration_filtering(): void
    {
        $filter = $this->createFilter();
        $result = $filter->actualDuration('90');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('actual_duration', $sql);
        $this->assertStringContainsString('>=', $sql);
        $this->assertContains('90', $bindings);
    }

    public function test_is_jit_true(): void
    {
        $filter = $this->createFilter();
        $result = $filter->isJit('1');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('is_jit', $sql);
        $this->assertStringContainsString('=', $sql);
        $this->assertContains('1', $bindings);
    }

    public function test_is_jit_false(): void
    {
        $filter = $this->createFilter();
        $result = $filter->isJit('0');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('is_jit', $sql);
        $this->assertStringContainsString('=', $sql);
        $this->assertContains('0', $bindings);
    }

    public function test_account_name_uses_like_filter(): void
    {
        $filter = $this->createFilter();
        $result = $filter->accountName('admin');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('account_name', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%admin%', $bindings);
    }

    public function test_jit_vault_path_uses_like_filter(): void
    {
        $filter = $this->createFilter();
        $result = $filter->jitVaultPath('/vault/secret');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('jit_vault_path', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%/vault/secret%', $bindings);
    }

    public function test_session_note_uses_like_filter(): void
    {
        $filter = $this->createFilter();
        $result = $filter->sessionNote('emergency access');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('session_note', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains('%emergency access%', $bindings);
    }

    public function test_is_expired_filtering(): void
    {
        $filter = $this->createFilter();
        $result = $filter->isExpired('1');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('is_expired', $sql);
        $this->assertStringContainsString('=', $sql);
        $this->assertContains('1', $bindings);
    }

    public function test_is_terminated_filtering(): void
    {
        $filter = $this->createFilter();
        $result = $filter->isTerminated('0');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('is_terminated', $sql);
        $this->assertStringContainsString('=', $sql);
        $this->assertContains('0', $bindings);
    }

    public function test_is_checkin_filtering(): void
    {
        $filter = $this->createFilter();
        $result = $filter->isCheckin('1');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('is_checkin', $sql);
        $this->assertStringContainsString('=', $sql);
        $this->assertContains('1', $bindings);
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
        $result = $filter->status('active,pending,completed');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('status', $sql);
        $this->assertStringContainsString('in', strtolower($sql));
        $this->assertContains('active', $bindings);
        $this->assertContains('pending', $bindings);
        $this->assertContains('completed', $bindings);
    }

    public function test_checkin_at_timestamp(): void
    {
        $filter = $this->createFilter();
        $result = $filter->checkinAt('2023-11-01,2023-11-30');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('checkin_at', $sql);
        $this->assertStringContainsString('between', strtolower($sql));
        $this->assertContains('2023-11-01', $bindings);
        $this->assertContains('2023-11-30', $bindings);
    }

    public function test_terminated_at_timestamp(): void
    {
        $filter = $this->createFilter();
        $result = $filter->terminatedAt('2023-10-01');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('terminated_at', $sql);
        $this->assertStringContainsString('>=', $sql);
        $this->assertContains('2023-10-01', $bindings);
    }

    public function test_ended_at_timestamp(): void
    {
        $filter = $this->createFilter();
        $result = $filter->endedAt('-2023-12-31');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('ended_at', $sql);
        $this->assertStringContainsString('<=', $sql);
        $this->assertContains('2023-12-31', $bindings);
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

    public function test_apply_with_multiple_filters(): void
    {
        $filter = $this->createFilter([
            'orgId' => '123',
            'assetId' => '456,789',
            'requesterId' => '100',
            'status' => 'active,pending',
            'isJit' => '1',
            'isExpired' => '0',
            'accountName' => 'admin',
            'startDatetime' => '2023-01-01,2023-12-31',
        ]);

        $result = $filter->apply($this->builder);

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        // Check that all filters are applied
        $this->assertStringContainsString('org_id', $sql);
        $this->assertStringContainsString('asset_id', $sql);
        $this->assertStringContainsString('requester_id', $sql);
        $this->assertStringContainsString('status', $sql);
        $this->assertStringContainsString('is_jit', $sql);
        $this->assertStringContainsString('is_expired', $sql);
        $this->assertStringContainsString('account_name', $sql);
        $this->assertStringContainsString('start_datetime', $sql);

        // Check bindings contain expected values
        $this->assertContains('123', $bindings);
        $this->assertContains('456', $bindings);
        $this->assertContains('789', $bindings);
        $this->assertContains('100', $bindings);
        $this->assertContains('active', $bindings);
        $this->assertContains('pending', $bindings);
        $this->assertContains('1', $bindings);
        $this->assertContains('0', $bindings);
        $this->assertContains('%admin%', $bindings);
    }

    public function test_methods_return_builder_instance(): void
    {
        $filter = $this->createFilter();

        $this->assertInstanceOf(Builder::class, $filter->orgId('123'));
        $this->assertInstanceOf(Builder::class, $filter->requestId('456'));
        $this->assertInstanceOf(Builder::class, $filter->assetId('789'));
        $this->assertInstanceOf(Builder::class, $filter->requesterId('100'));
        $this->assertInstanceOf(Builder::class, $filter->startDatetime('2023-01-01'));
        $this->assertInstanceOf(Builder::class, $filter->endDatetime('2023-01-01'));
        $this->assertInstanceOf(Builder::class, $filter->scheduledEndDatetime('2023-01-01'));
        $this->assertInstanceOf(Builder::class, $filter->requestedDuration('60'));
        $this->assertInstanceOf(Builder::class, $filter->actualDuration('60'));
        $this->assertInstanceOf(Builder::class, $filter->isJit('1'));
        $this->assertInstanceOf(Builder::class, $filter->accountName('admin'));
        $this->assertInstanceOf(Builder::class, $filter->jitVaultPath('/vault'));
        $this->assertInstanceOf(Builder::class, $filter->sessionNote('note'));
        $this->assertInstanceOf(Builder::class, $filter->isExpired('0'));
        $this->assertInstanceOf(Builder::class, $filter->isTerminated('0'));
        $this->assertInstanceOf(Builder::class, $filter->isCheckin('0'));
        $this->assertInstanceOf(Builder::class, $filter->status('active'));
        $this->assertInstanceOf(Builder::class, $filter->checkinAt('2023-01-01'));
        $this->assertInstanceOf(Builder::class, $filter->terminatedAt('2023-01-01'));
        $this->assertInstanceOf(Builder::class, $filter->endedAt('2023-01-01'));
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
        $filter = $this->createFilter(['sort' => 'start_datetime,-status,requester_id']);
        $result = $filter->apply($this->builder);

        $sql = strtolower($result->toSql());
        $this->assertStringContainsString('order by `start_datetime` asc', $sql);
        $this->assertStringContainsString('`status` desc', $sql);
        $this->assertStringContainsString('`requester_id` asc', $sql);
    }

    public function test_boolean_filters_with_string_values(): void
    {
        $filter = $this->createFilter();

        // Test various boolean representations
        $result1 = $filter->isJit('true');
        $bindings1 = $result1->getBindings();
        $this->assertContains('true', $bindings1);

        $result2 = $filter->isExpired('false');
        $bindings2 = $result2->getBindings();
        $this->assertContains('false', $bindings2);
    }

    public function test_duration_filtering_with_numbers(): void
    {
        $filter = $this->createFilter();

        $result = $filter->requestedDuration('30,180');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('requested_duration', $sql);
        $this->assertContains('30', $bindings);
        $this->assertContains('180', $bindings);
    }

    public function test_datetime_with_time_components(): void
    {
        $filter = $this->createFilter();

        $result = $filter->startDatetime('2023-01-01 09:00:00,2023-01-01 17:00:00');

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('start_datetime', $sql);
        $this->assertStringContainsString('between', strtolower($sql));
        $this->assertContains('2023-01-01 09:00:00', $bindings);
        $this->assertContains('2023-01-01 17:00:00', $bindings);
    }

    public function test_session_note_with_special_characters(): void
    {
        $filter = $this->createFilter();

        $result = $filter->sessionNote("Emergency access for user's request");

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        $this->assertStringContainsString('session_note', $sql);
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertContains("%Emergency access for user's request%", $bindings);
    }

    public function test_complex_session_filtering_scenario(): void
    {
        $filter = $this->createFilter([
            'orgId' => '1',
            'status' => 'active',
            'isJit' => '1',
            'isExpired' => '0',
            'isTerminated' => '0',
            'startDatetime' => '2023-11-01,2023-11-30',
            'requestedDuration' => '60',
            'accountName' => 'admin',
        ]);

        $result = $filter->apply($this->builder);

        $sql = $result->toSql();
        $bindings = $result->getBindings();

        // Verify multiple conditions are applied
        $this->assertStringContainsString('org_id', $sql);
        $this->assertStringContainsString('status', $sql);
        $this->assertStringContainsString('is_jit', $sql);
        $this->assertStringContainsString('is_expired', $sql);
        $this->assertStringContainsString('is_terminated', $sql);
        $this->assertStringContainsString('start_datetime', $sql);
        $this->assertStringContainsString('requested_duration', $sql);
        $this->assertStringContainsString('account_name', $sql);

        // Check specific values are bound
        $this->assertContains('1', $bindings);
        $this->assertContains('active', $bindings);
        $this->assertContains('0', $bindings);
        $this->assertContains('%admin%', $bindings);
    }
}
