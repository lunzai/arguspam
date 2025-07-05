<?php

namespace App\Policies;

use App\Models\Org;
use App\Models\User;

class UserOrgPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission('userorg:viewany');
    }

    public function view(User $user, Org $org): bool
    {
        return $user->hasAnyPermission('userorg:view', $org);
    }
}
