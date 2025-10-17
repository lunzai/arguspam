<?php

namespace App\Policies;

use App\Enums\SessionStatus;
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
        // TODO: Add $user->canAuditAsset($user, $session->asset)
        if ($this->viewAny($user) || $user->canApprove($session->asset)) {
            return true;
        }
        if (!$user->hasPermissionTo('session:view')) {
            return false;
        }
        if (!$session->requester->is($user)) {
            return false;
        }
        return true;
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
            ($user->canApprove($session->asset) && $user->hasPermissionTo('session:update'));
    }

    public function terminateAny(User $user): bool
    {
        return $user->hasPermissionTo('session:terminateany');
    }

    public function terminate(User $user, Session $session): bool
    {
        if ($this->terminateAny($user)) {
            return true;
        }
        if (!$user->hasPermissionTo('session:terminate')) {
            return false;
        }
        if (!$session->requester->isNot($user)) {
            return false;
        }
        if (!$user->canApprove($session->asset)) {
            return false;
        }
        if ($session->status !== SessionStatus::STARTED) {
            return false;
        }
        return true;
    }

    public function retrieveSecret(User $user, Session $session): bool
    {
        if (!$user->hasPermissionTo('session:retrievesecret')) {
            return false;
        }
        if (!$session->requester->is($user)) {
            return false;
        }
        if (!$session->isActive()) {
            return false;
        }
        return true;
    }

    public function start(User $user, Session $session): bool
    {
        if (!$user->hasPermissionTo('session:start')) {
            return false;
        }
        if (!$session->requester->is($user)) {
            return false;
        }
        if (!$session->canBeStarted()) {
            return false;
        }
        return true;
    }

    public function end(User $user, Session $session): bool
    {
        if (!$user->hasPermissionTo('session:end')) {
            return false;
        }
        if ($session->status !== SessionStatus::STARTED) {
            return false;
        }
        if (!$session->requester->is($user)) {
            return false;
        }
        return true;
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
