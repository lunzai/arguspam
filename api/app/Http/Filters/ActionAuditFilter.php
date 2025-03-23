<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class ActionAuditFilter extends QueryFilter
{
    protected array $sortable = [
        'org_id',
        'user_id',
        'action_type',
        'entity_type',
        'entity_id',
        'description',
        'previous_state',
        'new_state',
        'ip_address',
        'user_agent',
        'additional_info',
        'created_at',
    ];

    public function orgId(string $value): Builder
    {
        return $this->filterEqualOrIn('org_id', $value);
    }

    public function userId(string $value): Builder
    {
        return $this->filterEqualOrIn('user_id', $value);
    }

    public function actionType(string $value): Builder
    {
        return $this->filterEqualOrIn('action_type', $value);
    }

    public function entityType(string $value): Builder
    {
        return $this->filterEqualOrIn('entity_type', $value);
    }

    public function entityId(string $value): Builder
    {
        return $this->filterEqualOrIn('entity_id', $value);
    }

    public function description(string $value): Builder
    {
        return $this->filterLike('description', $value);
    }

    public function ipAddress(string $value): Builder
    {
        return $this->filterLike('ip_address', $value);
    }

    public function userAgent(string $value): Builder
    {
        return $this->filterLike('user_agent', $value);
    }

    public function createdAt(string $value): Builder
    {
        return $this->filterTimestamp('created_at', $value);
    }
}
