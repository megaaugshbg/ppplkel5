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
        // pengguna yang sudah login dan membuka /login atau /register diarahkan ke pencarian parkir
        $middleware->redirectUsersTo('/cari-parkir');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
