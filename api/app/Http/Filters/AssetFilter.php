<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class AssetFilter extends QueryFilter
{
    protected array $sortable = [
        'orgId',
        'name',
        'description',
        'status',
        'host',
        'port',
        'dbms',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
    ];

    public function orgId(string $value): Builder
    {
        return $this->filterEqualOrIn('org_id', $value);
    }

    public function name(string $value): Builder
    {
        return $this->filterLike('name', $value);
    }

    public function description(string $value): Builder
    {
        return $this->filterLike('description', $value);
    }

    public function status(string $value): Builder
    {
        return $this->filterEqualOrIn('status', $value);
    }

    public function host(string $value): Builder
    {
        return $this->filterLike('host', $value);
    }

    public function port(string $value): Builder
    {
        return $this->filterEqual('port', $value);
    }

    public function dbms(string $value): Builder
    {
        return $this->filterEqualOrIn('dbms', $value);
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
