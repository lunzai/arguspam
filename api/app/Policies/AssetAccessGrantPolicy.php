<?php

namespace App\Policies;

use App\Models\User;

class AssetAccessGrantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission('assetaccessgrant:viewany');
    }

    public function view(User $user): bool
    {
        return $this->viewAny($user) || $user->hasAnyPermission('assetaccessgrant:view');
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission('assetaccessgrant:create');
    }

    public function updateAny(User $user): bool
    {
        return $user->hasAnyPermission('assetaccessgrant:updateany');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasAnyPermission('assetaccessgrant:deleteany');
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasAnyPermission('assetaccessgrant:restoreany');
    }
}
