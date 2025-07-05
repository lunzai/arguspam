<?php

namespace App\Policies;

use App\Models\Org;
use App\Models\User;

class OrgUserPolicy
{
    public function viewAny(User $user, Org $org): bool
    {
        return $user->hasAnyPermission('orguser:viewany', $org);
    }

    public function create(User $user, Org $org): bool
    {
        return $user->hasAnyPermission('orguser:create', $org);
    }

    public function delete(User $user, Org $org): bool
    {
        return $user->hasAnyPermission('orguser:delete', $org);
    }
}