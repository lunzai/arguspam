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
        return $this->viewAny($user) ||
            ($user->id === $model->id && $user->hasAnyPermission('user:view'));
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
        return $this->updateAny($user) ||
            ($user->id === $model->id && $user->hasAnyPermission('user:update'));
    }

    public function deleteAny(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }
        return $user->hasAnyPermission('user:deleteany');
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasAnyPermission('user:restoreany');
    }
}
