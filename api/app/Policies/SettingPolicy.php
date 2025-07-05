<?php

namespace App\Policies;

use App\Models\User;
use App\Models\setting;

class SettingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission('setting:viewany');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, setting $setting): bool
    {
        return $user->hasAnyPermission('setting:view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission('setting:create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, setting $setting): bool
    {
        return $user->hasAnyPermission('setting:update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, setting $setting): bool
    {
        return $user->hasAnyPermission('setting:delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, setting $setting): bool
    {
        return $user->hasAnyPermission('setting:restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, setting $setting): bool
    {
        return $user->hasAnyPermission('setting:forcedelete');
    }
}
