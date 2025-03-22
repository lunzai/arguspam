<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserGroup;

class UserGroupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission('usergroup:viewany');
    }

    public function view(User $user, UserGroup $userGroup): bool
    {
        return $this->viewAny($user) ||
            ($user->groups->contains($userGroup) && $user->hasAnyPermission('usergroup:view'));
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission('usergroup:create');
    }

    public function updateAny(User $user): bool
    {
        return $user->hasAnyPermission('usergroup:updateany');
    }

    public function update(User $user, UserGroup $userGroup): bool
    {
        return $this->updateAny($user) ||
            ($user->groups->contains($userGroup) && $user->hasAnyPermission('usergroup:update'));
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasAnyPermission('usergroup:deleteany');
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasAnyPermission('usergroup:restoreany');
    }
}
