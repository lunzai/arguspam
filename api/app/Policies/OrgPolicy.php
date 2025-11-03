<?php

namespace App\Policies;

use App\Models\Org;
use App\Models\User;

class OrgPolicy
{
    public function view(User $user): bool
    {
        return $user->hasAnyPermission('org:view');
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission('org:create');
    }

    public function update(User $user): bool
    {
        return $user->hasAnyPermission('org:updateany');
    }

    public function delete(User $user): bool
    {
        return $user->hasAnyPermission('org:deleteany');
    }

    public function listUsers(User $user, Org $org): bool
    {
        return $user->hasAnyPermission('org:listusers');
    }

    public function addUser(User $user, Org $org): bool
    {
        return $user->hasAnyPermission('org:adduser');
    }

    public function removeUser(User $user, Org $org): bool
    {
        return $user->hasAnyPermission('org:removeuser');
    }

    public function listUserGroups(User $user, Org $org): bool
    {
        return $user->hasAnyPermission('org:listusergroups');
    }
}
