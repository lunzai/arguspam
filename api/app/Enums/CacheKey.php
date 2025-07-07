<?php

namespace App\Enums;

enum CacheKey: string
{
    // IN USE
    case USER_ORG = 'user:org';
    case USER_ROLES = 'user:role';
    case USER_PERMISSIONS = 'user:permission';

    // ROLE
    case ROLE_PERMISSIONS = 'role:permission';

    // PERMISSION
    case PERMISSIONS = 'permissions';

    // DASHBOARD
    case ORG_USERS_COUNT = 'org:user:count';
    case ORG_USER_GROUPS_COUNT = 'org:user_group:count';
    case ORG_ASSETS_COUNT = 'org:asset:count';
    case ORG_REQUESTS_COUNT = 'org:request:count';
    case ORG_REQUESTS_PENDING_COUNT = 'org:request:pending:count';
    case ORG_SESSIONS_COUNT = 'org:session:count';
    case ORG_SESSIONS_SCHEDULED_COUNT = 'org:session:scheduled:count';
    case ORG_SESSIONS_ACTIVE_COUNT = 'org:session:active:count';

    // NOT SURE

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
