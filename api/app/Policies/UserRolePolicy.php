<?php

namespace App\Policies;

use App\Models\User;

class UserRolePolicy
{
    public function create(User $user): bool
    {
        return $user->hasAnyPermission('userrole:create');
    }

    public function delete(User $user): bool
    {
        return $user->hasAnyPermission('userrole:delete');
    }
}
