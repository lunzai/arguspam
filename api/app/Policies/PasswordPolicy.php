<?php

namespace App\Policies;

use App\Models\User;

class PasswordPolicy
{
    public function update(User $user): bool
    {
        return $user->hasAnyPermission('password:update');
    }
}
