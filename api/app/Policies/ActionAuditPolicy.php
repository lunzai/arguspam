<?php

namespace App\Policies;

use App\Models\User;

class ActionAuditPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission('actionaudit:viewany');
    }
}
