<?php

namespace App\Policies;

use App\Models\User;

class PasswordPolicy
{
    public function change(User $user): bool
    {
        return $user->hasAnyPermission('password:change');
    }

    public function resetAny(User $user): bool
    {
        return $user->hasAnyPermission('password:resetany');
    }
}
