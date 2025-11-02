<?php

namespace App\Policies;

use App\Enums\AssetAccessRole;
use App\Models\Asset;
use App\Models\User;

class AssetPolicy
{
    public function view(User $user): bool
    {
        return $user->hasAnyPermission('asset:view');
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission('asset:create');
    }

    public function update(User $user, Asset $asset): bool
    {
        return $user->hasAnyPermission('asset:update');
    }

    public function delete(User $user, Asset $asset): bool
    {
        return $user->hasAnyPermission('asset:delete');
    }

    // TODO: REMOVE
    // public function request(User $user, Asset $asset): bool
    // {
    //     return $this->hasAccessGrant($user, $asset, AssetAccessRole::REQUESTER);
    // }

    // public function approve(User $user, Asset $asset): bool
    // {
    //     return $this->hasAccessGrant($user, $asset, AssetAccessRole::APPROVER);
    // }

    // private function hasAccessGrant(User $user, Asset $asset, AssetAccessRole $role): bool
    // {
    //     return $asset->accessGrants()
    //         ->where(function ($query) use ($user) {
    //             $query->where(function ($q) use ($user) {
    //                 // Direct user grant
    //                 $q->where('user_id', $user->id)
    //                     ->whereNull('user_group_id');
    //             })
    //                 ->orWhereIn('user_group_id', function ($q) use ($user) {
    //                     // User's group grants
    //                     $q->select('user_group_id')
    //                         ->from('user_user_group')
    //                         ->where('user_id', $user->id);
    //                 });
    //         })
    //         ->where('role', $role)
    //         ->exists();
    // }

    public function addAccessGrant(User $user, Asset $asset): bool
    {
        return $user->hasAnyPermission('asset:addaccessgrant');
    }

    public function removeAccessGrant(User $user, Asset $asset): bool
    {
        return $user->hasAnyPermission('asset:removeaccessgrant');
    }

    public function updateAdminAccount(User $user, Asset $asset): bool
    {
        return $user->hasAnyPermission('asset:updateadminaccount');
    }

    public function viewRequestable(User $user): bool
    {
        return $user->hasAnyPermission('asset:viewrequestable');
    }
}
