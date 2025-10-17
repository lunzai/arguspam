<?php

use Illuminate\Support\Facades\Schedule;

Schedule::everyMinute()
    ->onOneServer()
    ->group(function () {
        Schedule::command('pam:request:expired');
        Schedule::command('pam:session:expired');
    });
