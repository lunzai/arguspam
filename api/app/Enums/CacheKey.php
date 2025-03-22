<?php

namespace App\Enums;

enum CacheKey: string
{
    case USER_PERMISSIONS = 'user_permissions';
    case USER_ROLES = 'user_roles';

    public function key(int $id): string
    {
        return "{$this->value}:{$id}";
    }
}
