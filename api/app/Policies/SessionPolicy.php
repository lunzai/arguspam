<?php

namespace App\Policies;

use App\Models\Session;
use App\Models\User;

class SessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission('session:viewany');
    }

    public function view(User $user, Session $session): bool
    {
        return $this->viewAny($user) ||
            (
                ($session->requester->is($user) || $user->canApproveAsset($user, $session->asset)) &&
                $user->hasAnyPermission('session:view')
            );
    }

    public function auditAny(User $user): bool
    {
        return $user->hasAnyPermission('session:auditany');
    }

    public function updateAny(User $user): bool
    {
        return $user->hasAnyPermission('session:updateany');
    }

    public function update(User $user, Session $session): bool
    {
        return $this->updateAny($user) ||
            ($user->canApproveAsset($user, $session->asset) && $user->hasPermissionTo('session:update'));
    }

    public function terminateAny(User $user): bool
    {
        return $user->hasPermissionTo('session:terminateany');
    }

    public function terminate(User $user, Session $session): bool
    {
        return $this->terminateAny($user) ||
            (
                $session->requester->isNot($user) &&
                $user->canApproveAsset($user, $session->asset) &&
                $user->hasPermissionTo('session:terminate')
            );
    }

    public function retrieveSecret(User $user, Session $session): bool
    {
        return $session->requester->is($user) && $user->hasPermissionTo('session:retrievesecret');
    }

    public function start(User $user, Session $session): bool
    {
        return $session->requester->is($user) && $user->hasPermissionTo('session:start');
    }

    public function end(User $user, Session $session): bool
    {
        return $session->requester->is($user) && $user->hasPermissionTo('session:end');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasPermissionTo('session:deleteany');
    }

    public function restoreAny(User $user): bool
    {
        return $user->hasPermissionTo('session:restoreany');
    }
}
