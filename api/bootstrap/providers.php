<?php

use App\Providers\AppServiceProvider;
use App\Providers\HorizonServiceProvider;
use App\Providers\JitServiceProvider;
use App\Providers\TelescopeServiceProvider;

return [
    AppServiceProvider::class,
    HorizonServiceProvider::class,
    JitServiceProvider::class,
    TelescopeServiceProvider::class,
];
