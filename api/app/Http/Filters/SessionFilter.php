<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class SessionFilter extends QueryFilter
{
    protected array $sortable = [
        'orgId',
        'requestId',
        'assetId',
        'requesterId',
        'startDatetime' => 'start_datetime',
        'endDatetime' => 'end_datetime',
        'scheduledEndDatetime' => 'scheduled_end_datetime',
        'requestedDuration' => 'requested_duration',
        'actualDuration' => 'actual_duration',
        'isJit' => 'is_jit',
        'accountName' => 'account_name',
        'isExpired' => 'is_expired',
        'isTerminated' => 'is_terminated',
        'isCheckin' => 'is_checkin',
        'status',
        'checkinAt' => 'checkin_at',
        'terminatedAt' => 'terminated_at',
        'endedAt' => 'ended_at',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
    ];

    public function orgId(string $value): Builder
    {
        return $this->filterEqualOrIn('org_id', $value);
    }

    public function requestId(string $value): Builder
    {
        return $this->filterEqualOrIn('request_id', $value);
    }

    public function assetId(string $value): Builder
    {
        return $this->filterEqualOrIn('asset_id', $value);
    }

    public function requesterId(string $value): Builder
    {
        return $this->filterEqualOrIn('requester_id', $value);
    }

    public function startDatetime(string $value): Builder
    {
        return $this->filterTimestamp('start_datetime', $value);
    }

    public function endDatetime(string $value): Builder
    {
        return $this->filterTimestamp('end_datetime', $value);
    }

    public function scheduledEndDatetime(string $value): Builder
    {
        return $this->filterTimestamp('scheduled_end_datetime', $value);
    }

    public function requestedDuration(string $value): Builder
    {
        return $this->filterTimestamp('requested_duration', $value);
    }

    public function actualDuration(string $value): Builder
    {
        return $this->filterTimestamp('actual_duration', $value);
    }

    public function isJit(string $value): Builder
    {
        return $this->filterEqual('is_jit', $value);
    }

    public function accountName(string $value): Builder
    {
        return $this->filterLike('account_name', $value);
    }

    public function jitVaultPath(string $value): Builder
    {
        return $this->filterLike('jit_vault_path', $value);
    }

    public function sessionNote(string $value): Builder
    {
        return $this->filterLike('session_note', $value);
    }

    public function isExpired(string $value): Builder
    {
        return $this->filterEqual('is_expired', $value);
    }

    public function isTerminated(string $value): Builder
    {
        return $this->filterEqual('is_terminated', $value);
    }

    public function isCheckin(string $value): Builder
    {
        return $this->filterEqual('is_checkin', $value);
    }

    public function status(string $value): Builder
    {
        return $this->filterEqualOrIn('status', $value);
    }

    public function checkinAt(string $value): Builder
    {
        return $this->filterTimestamp('checkin_at', $value);
    }

    public function terminatedAt(string $value): Builder
    {
        return $this->filterTimestamp('terminated_at', $value);
    }

    public function endedAt(string $value): Builder
    {
        return $this->filterTimestamp('ended_at', $value);
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
