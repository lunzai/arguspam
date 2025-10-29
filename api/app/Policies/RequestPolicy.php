<?php

namespace App\Policies;

use App\Models\Request;
use App\Models\User;

class RequestPolicy
{
    public function view(User $user, Request $request): bool
    {
        return $user->hasAnyPermission('request:view');
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission('request:create');
    }

    public function approveAny(User $user): bool
    {
        return $user->hasPermissionTo('request:approveany');
    }

    public function approve(User $user, Request $request): bool
    {
        if ($this->approveAny($user)) {
            return true;
        }
        if (!$user->hasPermissionTo('request:approve')) {
            return false;
        }
        // $request->requester->isNot($user) && // Allow for now, considering small team use case
        return $user->canApproveAsset($user, $request->asset);
    }

    public function rejectAny(User $user): bool
    {
        return $user->hasPermissionTo('request:rejectany');
    }

    public function reject(User $user, Request $request): bool
    {
        if ($this->rejectAny($user)) {
            return true;
        }
        if (!$user->hasPermissionTo('request:reject')) {
            return false;
        }
        // $request->requester->isNot($user) && // Allow for now, considering small team use case
        return $user->canApproveAsset($user, $request->asset);
    }

    public function cancelAny(User $user): bool
    {
        return $user->hasPermissionTo('request:cancelany');
    }

    public function cancel(User $user, Request $request): bool
    {
        if ($this->cancelAny($user)) {
            return true;
        }
        if (!$user->hasPermissionTo('request:cancel')) {
            return false;
        }
        return $request->requester->is($user);
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
