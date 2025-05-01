<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class SessionAuditFilter extends QueryFilter
{
    protected array $sortable = [
        'org_id',
        'session_id',
        'request_id',
        'asset_id',
        'user_id',
        'query_text',
        'query_timestamp',
        'created_at',
    ];

    public function orgId(string $value): Builder
    {
        return $this->filterEqualOrIn('org_id', $value);
    }

    public function sessionId(string $value): Builder
    {
        return $this->filterEqualOrIn('session_id', $value);
    }

    public function requestId(string $value): Builder
    {
        return $this->filterEqualOrIn('request_id', $value);
    }

    public function assetId(string $value): Builder
    {
        return $this->filterEqualOrIn('asset_id', $value);
    }

    public function userId(string $value): Builder
    {
        return $this->filterEqualOrIn('user_id', $value);
    }

    public function queryText(string $value): Builder
    {
        return $this->filterLike('query_text', $value);
    }

    public function queryTimestamp(string $value): Builder
    {
        return $this->filterTimestamp('query_timestamp', $value);
    }

    public function createdAt(string $value): Builder
    {
        return $this->filterTimestamp('created_at', $value);
    }
}
