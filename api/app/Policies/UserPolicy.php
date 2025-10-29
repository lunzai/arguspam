<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission('user:viewany');
    }

    public function view(User $user, User $model): bool
    {
        if ($this->viewAny($user)) {
            return true;
        }
        if (!$user->hasAnyPermission('user:view')) {
            return false;
        }
        return $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission('user:create');
    }

    public function updateAny(User $user): bool
    {
        return $user->hasAnyPermission('user:updateany');
    }

    public function update(User $user, User $model): bool
    {
        if ($this->updateAny($user)) {
            return true;
        }
        if (!$user->hasAnyPermission('user:update')) {
            return false;
        }
        return $user->id === $model->id;
    }

    public function deleteAny(User $user, User $model): bool
    {
        return $user->hasAnyPermission('user:deleteany');
    }

    public function resetPasswordAny(User $user): bool
    {
        return $user->hasAnyPermission('user:resetpasswordany');
    }

    public function changePassword(User $user): bool
    {
        return $user->hasAnyPermission('user:changepassword');
    }

    public function enrollTwoFactorAuthenticationAny(User $user): bool
    {
        return $user->hasAnyPermission('user:enrolltwofactorauthenticationany');
    }

    public function enrollTwoFactorAuthentication(User $user, User $model): bool
    {
        if ($this->enrollTwoFactorAuthenticationAny($user)) {
            return true;
        }
        if (!$user->hasPermissionTo('user:enrolltwofactorauthentication')) {
            return false;
        }
        return $user->is($model);
    }
}
