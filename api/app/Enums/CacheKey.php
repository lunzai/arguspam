<?php

namespace App\Enums;

enum CacheKey: string
{
    // IN USE
    case AUTH_2FA_TEMP_KEY = 'auth:2fa:temp_key';

    // USER
    case USERS = 'users';
    case USER_ORG = 'user:org';
    case USER_ROLES = 'user:role';
    case USER_PERMISSIONS = 'user:permission';

    // USER GROUP
    case USER_GROUPS = 'user_groups';

    // ROLE
    case ROLES = 'roles';
    case ROLE_PERMISSIONS = 'role:permission';

    // PERMISSION
    case PERMISSIONS = 'permissions';

    // ACCESS RESTRICTION
    case ACCESS_RESTRICTIONS = 'access_restrictions';
    case ACCESS_RESTRICTION_USERS = 'access_restriction:users';
    case ACCESS_RESTRICTION_USER_GROUPS = 'access_restriction:user_groups';

    // DASHBOARD
    case ORG_USERS_COUNT = 'org:user:count';
    case ORG_USER_GROUPS_COUNT = 'org:user_group:count';
    case ORG_ASSETS_COUNT = 'org:asset:count';
    case ORG_REQUESTS_COUNT = 'org:request:count';
    case ORG_REQUESTS_PENDING_COUNT = 'org:request:pending:count';
    case ORG_SESSIONS_COUNT = 'org:session:count';
    case ORG_SESSIONS_SCHEDULED_COUNT = 'org:session:scheduled:count';
    case ORG_SESSIONS_ACTIVE_COUNT = 'org:session:active:count';

    // ORG
    case ORGS = 'orgs';
    case ORG_USERS = 'org_users';

    // ASSET
    case ASSETS = 'assets';
    case ASSET_ACCESS_GRANTS = 'asset:access_grants';

    // ACTION AUDIT
    case ACTION_AUDITS = 'action_audits';

    // REQUEST
    case REQUESTS = 'requests';

    // SESSION
    case SESSIONS = 'sessions';
    case SESSION_AUDITS = 'session_audits';

    // NOT SURE
    case SETTING_VALUE = 'settings:value';
    case SETTING_KEY = 'settings:key';
    case SETTING_ALL = 'settings:all';
    case SETTING_GROUP = 'settings:group';
    case SETTING_GROUP_ALL = 'settings:group:all';

    public function key($id): string
    {
        return "{$this->value}:{$id}";
    }

}
