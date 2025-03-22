<?php

namespace App\Policies;

use App\Models\SessionAudit;
use App\Models\User;

class SessionAuditPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission('sessionaudit:viewany');
    }

    public function view(User $user, SessionAudit $sessionAudit): bool
    {
        return $this->viewAny($user) ||
            (
                $user->canAccessAsset($user, $sessionAudit->session->asset) &&
                $user->hasAnyPermission('sessionaudit:view')
            );
    }
}
