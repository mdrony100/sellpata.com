<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\ResolveVisitorIdentity;
use App\Http\Middleware\ServeCustomDomainStorefront;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The visitor token is an opaque random secret that is only ever stored
        // hashed server-side, so it travels as a plain HttpOnly cookie (the same
        // model as Laravel's own session cookie).
        $middleware->encryptCookies(except: ['sp_visitor']);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'visitor' => ResolveVisitorIdentity::class,
        ]);

        // After the session starts, so a verified custom domain can render the
        // storefront (including its CSRF token) without taking over other hosts.
        $middleware->appendToGroup('web', ServeCustomDomainStorefront::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
