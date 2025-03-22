<?php

namespace App\Policies;

use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission('role:viewany');
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission('role:create');
    }

    public function updateAny(User $user): bool
    {
        return $user->hasAnyPermission('role:updateany');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasAnyPermission('role:deleteany');
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasAnyPermission('role:restoreany');
    }
}
