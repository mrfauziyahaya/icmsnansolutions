<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Payment gateways POST server-to-server and cannot carry a CSRF token.
        // Authenticity is verified per-gateway inside each driver's verifyCallback().
        $middleware->validateCsrfTokens(except: [
            'webhooks/payments/*',
        ]);

        // Multi-domain: work out which site this request belongs to, then keep
        // each domain to the routes it is allowed to serve.
        $middleware->web(append: [
            \App\Http\Middleware\ResolveCurrentSite::class,
            \App\Http\Middleware\EnsureRouteAllowedForSite::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
