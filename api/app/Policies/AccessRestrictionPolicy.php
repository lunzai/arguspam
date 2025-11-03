<?php

namespace App\Policies;

use App\Models\AccessRestriction;
use App\Models\User;

class AccessRestrictionPolicy
{
    public function view(User $user, AccessRestriction $accessRestriction): bool
    {
        return $user->hasAnyPermission('accessrestriction:view');
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission('accessrestriction:create');
    }

    public function update(User $user, AccessRestriction $accessRestriction): bool
    {
        return $user->hasAnyPermission('accessrestriction:update');
    }

    public function delete(User $user, AccessRestriction $accessRestriction): bool
    {
        return $user->hasAnyPermission('accessrestriction:deleteany');
    }

    public function listUsers(User $user, AccessRestriction $accessRestriction): bool
    {
        return $user->hasAnyPermission('accessrestriction:listusers');
    }

    public function addUser(User $user, AccessRestriction $accessRestriction): bool
    {
        return $user->hasAnyPermission('accessrestriction:adduser');
    }

    public function removeUser(User $user, AccessRestriction $accessRestriction): bool
    {
        return $user->hasAnyPermission('accessrestriction:removeuser');
    }

    public function listUserGroups(User $user, AccessRestriction $accessRestriction): bool
    {
        return $user->hasAnyPermission('accessrestriction:listusergroups');
    }

    public function addUserGroup(User $user, AccessRestriction $accessRestriction): bool
    {
        return $user->hasAnyPermission('accessrestriction:addusergroup');
    }

    public function removeUserGroup(User $user, AccessRestriction $accessRestriction): bool
    {
        return $user->hasAnyPermission('accessrestriction:removeusergroup');
    }
}
