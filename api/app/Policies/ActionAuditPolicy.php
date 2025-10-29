<?php

namespace App\Policies;

use App\Models\User;

class ActionAuditPolicy
{
    public function view(User $user): bool
    {
        return $user->hasAnyPermission('actionaudit:view');
    }
}
