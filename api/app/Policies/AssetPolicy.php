<?php

namespace App\Policies;

use App\Enums\AssetAccessRole;
use App\Models\Asset;
use App\Models\User;

class AssetPolicy
{
    public function request(User $user, Asset $asset): bool
    {
        return $this->hasAccessGrant($user, $asset, AssetAccessRole::REQUESTER);
    }

    public function approve(User $user, Asset $asset): bool
    {
        return $this->hasAccessGrant($user, $asset, AssetAccessRole::APPROVER);
    }

    private function hasAccessGrant(User $user, Asset $asset, AssetAccessRole $role): bool
    {
        return $asset->accessGrants()
            ->where(function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    // Direct user grant
                    $q->where('user_id', $user->id)
                        ->whereNull('user_group_id');
                })
                    ->orWhereIn('user_group_id', function ($q) use ($user) {
                        // User's group grants
                        $q->select('user_group_id')
                            ->from('user_user_group')
                            ->where('user_id', $user->id);
                    });
            })
            ->where('role', $role)
            ->exists();
    }

    // /**
    //  * Determine whether the user can view any models.
    //  */
    // public function viewAny(User $user): bool
    // {
    //     return false;
    // }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Asset $asset): bool
    {
        return $user->canRequest($asset) || $user->canApprove($asset);
    }

    // /**
    //  * Determine whether the user can create models.
    //  */
    // public function create(User $user): bool
    // {
    //     return false;
    // }

    // /**
    //  * Determine whether the user can update the model.
    //  */
    // public function update(User $user, Asset $asset): bool
    // {
    //     return false;
    // }

    // /**
    //  * Determine whether the user can delete the model.
    //  */
    // public function delete(User $user, Asset $asset): bool
    // {
    //     return false;
    // }

    // /**
    //  * Determine whether the user can restore the model.
    //  */
    // public function restore(User $user, Asset $asset): bool
    // {
    //     return false;
    // }

    // /**
    //  * Determine whether the user can permanently delete the model.
    //  */
    // public function forceDelete(User $user, Asset $asset): bool
    // {
    //     return false;
    // }
}
