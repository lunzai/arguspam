<?php

namespace App\Listeners;

use App\Events\UserLoggedIn;
use Illuminate\Support\Facades\Log;

class UpdateUserLastLogin
{
    public function handle(UserLoggedIn $event): void
    {
        $event->user->touch('last_login_at');
    }
}
