<?php

namespace App\Policies;

use App\Models\Request;
use App\Models\User;

class RequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission('request:viewany');
    }

    public function view(User $user, Request $request): bool
    {
        return $this->viewAny($user) ||
            ($request->requester->is($user) && $user->hasAnyPermission('request:view'));
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission('request:create');
    }

    public function updateAny(User $user): bool
    {
        return $user->hasAnyPermission('request:updateany');
    }

    public function update(User $user, Request $request): bool
    {
        return $this->updateAny($user) ||
            ($request->requester->is($user) && $user->hasAnyPermission('request:update'));
    }

    public function approveAny(User $user): bool
    {
        return $user->hasPermissionTo('request:approveany');
    }

    public function approve(User $user, Request $request): bool
    {
        return $this->approveAny($user) ||
            (
                $request->requester->isNot($user) &&
                $user->canApproveAsset($user, $request->asset) &&
                $user->hasPermissionTo('request:approve')
            );
    }

    public function rejectAny(User $user): bool
    {
        return $user->hasPermissionTo('request:rejectany');
    }

    public function reject(User $user, Request $request): bool
    {
        return $this->rejectAny($user) ||
            (
                $request->requester->isNot($user) &&
                $user->canApproveAsset($user, $request->asset) &&
                $user->hasPermissionTo('request:reject')
            );
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('request:deleteany');
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasPermissionTo('request:restoreany');
    }
}
