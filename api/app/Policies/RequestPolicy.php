<?php

namespace App\Policies;

use App\Models\Request;
use App\Models\User;

class RequestPolicy
{
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
    public function view(User $user, Request $request): bool
    {
        return $user->isAuditor() ||
            $user->id === $request->user_id ||
            $user->canApprove($request->asset);
    }

    // /**
    //  * Determine whether the user can create models.
    //  */
    // public function create(User $user): bool
    // {
    //     return true;
    // }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Request $request): bool
    {
        return $user->id === $request->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Request $request): bool
    {
        return $user->id === $request->user_id;
    }

    // /**
    //  * Determine whether the user can restore the model.
    //  */
    // public function restore(User $user, Request $request): bool
    // {
    //     return false;
    // }

    // /**
    //  * Determine whether the user can permanently delete the model.
    //  */
    // public function forceDelete(User $user, Request $request): bool
    // {
    //     return false;
    // }
}
