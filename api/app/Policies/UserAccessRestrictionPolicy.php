<?php

namespace App\Policies;

use App\Models\User;

class UserAccessRestrictionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('useraccessrestriction:viewany');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('useraccessrestriction:create');
    }

    public function updateAny(User $user): bool
    {
        return $user->hasPermissionTo('useraccessrestriction:updateany');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('useraccessrestriction:deleteany');
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasPermissionTo('useraccessrestriction:restoreany');
    }
}
