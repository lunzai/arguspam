<?php

namespace App\Policies;

use App\Models\User;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission('permission:viewany');
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission('permission:create');
    }

    public function updateAny(User $user): bool
    {
        return $user->hasAnyPermission('permission:updateany');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasAnyPermission('permission:deleteany');
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasAnyPermission('permission:restoreany');
    }
}
