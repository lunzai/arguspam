<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class RequestFilter extends QueryFilter
{
    protected array $sortable = [
        'orgId',
        'assetId',
        'assetAccountId',
        'requesterId',
        'startDatetime' => 'start_datetime',
        'endDatetime' => 'end_datetime',
        'duration',
        'scope',
        'isAccessSensitiveData' => 'is_access_sensitive_data',
        'approverRiskRating' => 'approver_risk_rating',
        'status',
        'approvedAt' => 'approved_at',
        'rejectedAt' => 'rejected_at',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
    ];

    public function orgId(string $value): Builder
    {
        return $this->filterEqualOrIn('org_id', $value);
    }

    public function assetId(string $value): Builder
    {
        return $this->filterEqualOrIn('asset_id', $value);
    }

    public function assetAccountId(string $value): Builder
    {
        return $this->filterEqualOrIn('asset_account_id', $value);
    }

    public function requesterId(string $value): Builder
    {
        return $this->filterEqualOrIn('requester_id', $value);
    }

    public function startDatetime(string $value): Builder
    {
        return $this->filterTimestamp('start_datetime', $value);
    }

    public function duration(string $value): Builder
    {
        return $this->filterTimestamp('duration', $value);
    }

    public function reason(string $value): Builder
    {
        return $this->filterLike('reason', $value);
    }

    public function intendedQuery(string $value): Builder
    {
        return $this->filterLike('intended_query', $value);
    }

    public function scope(string $value): Builder
    {
        return $this->filterEqualOrIn('scope', $value);
    }

    public function isAccessSensitiveData(string $value): Builder
    {
        return $this->filterEqual('is_access_sensitive_data', $value);
    }

    public function sensitiveDataNote(string $value): Builder
    {
        return $this->filterLike('sensitive_data_note', $value);
    }

    public function approverNote(string $value): Builder
    {
        return $this->filterLike('approver_note', $value);
    }

    public function approverRiskRating(string $value): Builder
    {
        return $this->filterEqualOrIn('approver_risk_rating', $value);
    }

    public function status(string $value): Builder
    {
        return $this->filterEqualOrIn('status', $value);
    }

    public function approvedAt(string $value): Builder
    {
        return $this->filterTimestamp('approved_at', $value);
    }

    public function rejectedAt(string $value): Builder
    {
        return $this->filterTimestamp('rejected_at', $value);
    }

    public function createdAt(string $value): Builder
    {
        return $this->filterTimestamp('created_at', $value);
    }

    public function updatedAt(string $value): Builder
    {
        return $this->filterTimestamp('updated_at', $value);
    }
}
