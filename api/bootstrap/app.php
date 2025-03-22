<?php

use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // TODO: Remove this middleware
        // $middleware->group('api', [
        //     ForceJsonResponse::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // TODO: Remove this exception handler
        // $exceptions->shouldRenderJsonWhen(function (Throwable $e, Request $request) {
        //     if ($request->is('api/*')) {
        //         return true;
        //     }

        //     return $request->expectsJson();
        // });
    })->create();
