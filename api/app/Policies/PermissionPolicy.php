<?php

namespace App\Policies;

use App\Models\User;

class PermissionPolicy
{
    public function view(User $user): bool
    {
        return $user->hasAnyPermission('permission:view');
    }
}
