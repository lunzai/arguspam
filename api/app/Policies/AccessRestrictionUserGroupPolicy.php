<?php

namespace App\Policies;

use App\Models\AccessRestriction;
use App\Models\User;

class AccessRestrictionUserGroupPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission('accessrestrictionuser:viewany');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission('accessrestrictionuser:create');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AccessRestriction $accessRestriction): bool
    {
        return $user->hasAnyPermission('accessrestrictionuser:delete');
    }
}
