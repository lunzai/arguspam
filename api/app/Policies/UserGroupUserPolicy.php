<?php

namespace App\Policies;

use App\Models\User;

class UserGroupUserPolicy
{
    public function create(User $user): bool
    {
        return $user->hasAnyPermission('usergroupuser:create');
    }

    public function delete(User $user): bool
    {
        return $user->hasAnyPermission('usergroupuser:delete');
    }
}
