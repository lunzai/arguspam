<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserGroup;

class UserGroupPolicy
{
    public function view(User $user, UserGroup $userGroup): bool
    {
        return $user->hasAnyPermission('usergroup:view');
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission('usergroup:create');
    }

    public function update(User $user, UserGroup $userGroup): bool
    {
        return $user->hasAnyPermission('usergroup:update');
    }

    public function delete(User $user, UserGroup $userGroup): bool
    {
        return $user->hasAnyPermission('usergroup:delete');
    }

    public function addUser(User $user, UserGroup $userGroup): bool
    {
        return $user->hasAnyPermission('usergroup:adduser');
    }

    public function removeUser(User $user, UserGroup $userGroup): bool
    {
        return $user->hasAnyPermission('usergroup:removeuser');
    }
}
