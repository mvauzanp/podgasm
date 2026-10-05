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
        $middleware->alias([
            'role'           => \App\Http\Middleware\RoleMiddleware::class,
            'olsera.webhook' => \App\Http\Middleware\VerifyOlseraWebhook::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'api/midtrans/notification',
            'midtrans/notification',
            'api/olsera/webhook',
            'api/biteship/webhook',
            'webhook/biteship',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
