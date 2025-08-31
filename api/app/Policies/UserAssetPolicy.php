<?php

namespace App\Policies;

use App\Models\User;

class UserAssetPolicy
{
    public function view(User $user): bool
    {
        return $user->hasAnyPermission('userasset:view');
    }
}
