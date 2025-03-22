<?php

namespace App\Policies;

use App\Models\AssetAccount;
use App\Models\User;

class AssetAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission('assetaccount:viewany');
    }

    public function view(User $user, AssetAccount $assetAccount): bool
    {
        return $this->viewAny($user) ||
            ($user->canAccessAsset($user, $assetAccount->asset) && $user->hasAnyPermission('assetaccount:view'));
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission('assetaccount:create');
    }

    public function updateAny(User $user): bool
    {
        return $user->hasAnyPermission('assetaccount:updateany');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasAnyPermission('assetaccount:deleteany');
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasAnyPermission('assetaccount:restoreany');
    }
}
