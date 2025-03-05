<?php

namespace App\Listeners;

use App\Events\UserLoggedIn;

class UpdateUserLastLogin
{
    public function handle(UserLoggedIn $event): void
    {
        $event->user->touch('last_login_at');
    }
}
