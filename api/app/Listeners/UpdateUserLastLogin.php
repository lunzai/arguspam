<?php

namespace App\Listeners;

use App\Events\UserLoggedIn;
use Illuminate\Support\Facades\Log;

class UpdateUserLastLogin
{
    public function handle(UserLoggedIn $event): void
    {
        Log::info('UpdateUserLastLogin handling', ['user_id' => $event->user->id]);
        $event->user->touch('last_login_at');
    }
}
