<?php

namespace Lunzai\QuerySearch;

use Illuminate\Support\ServiceProvider;
use Lunzai\QuerySearch\Support\FilterResolver;

class QuerySearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/query-search.php',
            'query-search'
        );

        $this->app->singleton(FilterResolver::class, function ($app) {
            return new FilterResolver($app);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/query-search.php' => config_path('query-search.php'),
            ], 'query-search-config');
        }
    }
}
