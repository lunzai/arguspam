<?php

namespace App\Enums;

enum CacheKey: string
{
    case USER_PERMISSIONS = 'user_permissions';
    case USER_ROLES = 'user_roles';
    case ORG_USERS = 'org_users';
    case SETTING_VALUE = 'settings:value';
    case SETTING_KEY = 'settings:key';
    case SETTING_ALL = 'settings:all';
    case SETTING_GROUP = 'settings:group';
    case SETTING_GROUP_ALL = 'settings:group:all';

    public function key(int $id): string
    {
        return "{$this->value}:{$id}";
    }
}
