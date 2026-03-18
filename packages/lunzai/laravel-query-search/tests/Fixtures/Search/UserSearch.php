<?php

namespace Lunzai\QuerySearch\Tests\Fixtures\Search;

use Lunzai\QuerySearch\Search;
use Lunzai\QuerySearch\Tests\Fixtures\Search\Filters\MfaFilter;

class UserSearch extends Search
{
    protected array $sortable = [
        'name',
        'email',
        'status',
        'created_at',
        'updated_at',
    ];

    protected array $includeable = ['roles'];

    protected array $countable = ['sessions'];

    protected function filters(): array
    {
        return [
            'status' => ['operator' => 'in',     'column' => 'status'],
            'email' => ['operator' => 'like',   'column' => 'email'],
            'created_at' => ['operator' => 'range',  'column' => 'created_at'],
            'role_id' => ['operator' => 'in',     'relation' => 'roles', 'column' => 'id'],
            'mfa' => ['operator' => 'custom', 'handler' => MfaFilter::class],
        ];
    }

    protected function rules(): array
    {
        return [
            'filter.status' => ['sometimes', 'string'],
            'filter.email' => ['sometimes', 'string', 'max:255'],
            'filter.mfa' => ['sometimes', 'string', 'regex:/^(active|pending|off)(,(active|pending|off))*$/'],
        ];
    }
}
