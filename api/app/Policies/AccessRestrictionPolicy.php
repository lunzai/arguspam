<?php

namespace App\Policies;

use App\Models\AccessRestriction;
use App\Models\User;

class AccessRestrictionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission('accessrestriction:viewany');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AccessRestriction $accessRestriction): bool
    {
        return $user->hasAnyPermission('accessrestriction:view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission('accessrestriction:create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AccessRestriction $accessRestriction): bool
    {
        return $user->hasAnyPermission('accessrestriction:update');
    }

    public function updateAny(User $user): bool
    {
        return $user->hasAnyPermission('accessrestriction:updateany');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AccessRestriction $accessRestriction): bool
    {
        return $user->hasAnyPermission('accessrestriction:delete');
    }
}
