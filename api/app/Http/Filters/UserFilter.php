<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class UserFilter extends QueryFilter
{
    public function orgId(string $value): Builder
    {
        $arr = explode(',', $value);
        if (count($arr) === 1) {
            return $this->builder->where('org_id', $arr[0]);
        }

        return $this->builder->whereIn('org_id', $arr);
    }

    public function name(string $value): Builder
    {
        return $this->builder->where('name', 'like', '%'.$value.'%');
    }

    public function email(string $value): Builder
    {
        return $this->builder->where('email', 'like', '%'.$value.'%');
    }

    public function status(string $value): Builder
    {
        $arr = explode(',', $value);
        if (count($arr) === 1) {
            return $this->builder->where('status', $arr[0]);
        }

        return $this->builder->whereIn('status', $arr);
    }

    public function createdAt(string $value): Builder
    {
        $arr = explode(',', $value);
        if (count($arr) > 1) {
            return $this->builder->whereBetween('created_at', [$arr[0], $arr[1]]);
        }
        if ($value[0] === '-') {
            return $this->builder->where('created_at', '<=', substr($value, 1));
        }

        return $this->builder->where('created_at', '>=', $value);
    }

    public function updatedAt(string $value): Builder
    {
        $arr = explode(',', $value);
        if (count($arr) > 1) {
            return $this->builder->whereBetween('updated_at', [$arr[0], $arr[1]]);
        }
        if ($value[0] === '-') {
            return $this->builder->where('updated_at', '<=', substr($value, 1));
        }

        return $this->builder->where('updated_at', '>=', $value);
    }

    public function lastLoginAt(string $value): Builder
    {
        $arr = explode(',', $value);
        if (count($arr) === 2) {
            return $this->builder->whereBetween('last_login_at', [$arr[0], $arr[1]]);
        }
        if ($value[0] === '-') {
            return $this->builder->where('last_login_at', '<=', substr($value, 1));
        }

        return $this->builder->where('last_login_at', '>=', $value);
    }

    public function twoFactorEnabled(string $value): Builder
    {
        return $this->builder->where('two_factor_enabled', $value);
    }
}
