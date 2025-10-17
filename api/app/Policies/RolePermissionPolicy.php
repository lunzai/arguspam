<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePermissionPolicy
{
    public function index(User $user, Role $role): bool
    {
        return $user->hasAnyPermission('rolepermission:index');
    }

    public function create(User $user, Role $role): bool
    {
        return $user->hasAnyPermission('rolepermission:create');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasAnyPermission('rolepermission:update');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->hasAnyPermission('rolepermission:delete');
    }
}
