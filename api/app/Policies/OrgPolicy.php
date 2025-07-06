<?php

namespace App\Policies;

use App\Models\Org;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class OrgPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission('org:viewany');
    }

    public function view(User $user, Org $org): bool
    {
        return $this->viewAny($user) ||
            ($user->inOrg($org) && $user->hasAnyPermission('org:view'));
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission('org:create');
    }

    public function updateAny(User $user): bool
    {
        return $user->hasAnyPermission('org:updateany');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasAnyPermission('org:deleteany');
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasAnyPermission('org:restoreany');
    }
}
