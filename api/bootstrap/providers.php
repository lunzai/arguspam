<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Services\Secrets\SecretsServiceProvider::class,
    Torann\GeoIP\GeoIPServiceProvider::class,
    Illuminate\Notifications\SlackChannelServiceProvider::class,
];
