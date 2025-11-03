<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function view(User $user): bool
    {
        return $user->hasAnyPermission('role:view');
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission('role:create');
    }

    public function update(User $user): bool
    {
        return $user->hasAnyPermission('role:update');
    }

    public function delete(User $user): bool
    {
        return $user->hasAnyPermission('role:delete');
    }

    public function listPermissions(User $user, Role $role): bool
    {
        return $user->hasAnyPermission('role:listpermissions');
    }

    public function updatePermissions(User $user, Role $role): bool
    {
        return $user->hasAnyPermission('role:updatepermissions');
    }
}
