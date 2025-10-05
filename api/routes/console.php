<?php

use Illuminate\Support\Facades\Schedule;

Schedule::everyMinute()
    ->onOneServer()
    ->group(function () {
        Schedule::command('request:expire');
        // TODO:
        // Process expired sessions
        // Clean up expired JIT accounts
    });
