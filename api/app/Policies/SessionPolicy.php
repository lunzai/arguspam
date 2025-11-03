<?php

namespace App\Policies;

use App\Models\Session;
use App\Models\User;

class SessionPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermissionTo('session:view');
    }

    public function permission(User $user, Session $session): bool
    {
        return $user->hasPermissionTo('session:permission');
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
        return true;
    }

    public function retrieveSecret(User $user, Session $session): bool
    {
        return $user->hasPermissionTo('session:retrievesecret') && $session->requester->is($user);
    }

    public function start(User $user, Session $session): bool
    {
        return $user->hasPermissionTo('session:start') && $session->requester->is($user);
    }

    public function end(User $user, Session $session): bool
    {
        return $user->hasPermissionTo('session:end') && $session->requester->is($user);
    }

    public function cancel(User $user, Session $session): bool
    {
        return $user->hasPermissionTo('session:cancel') && $session->requester->is($user);
    }
}
