<?php

use App\Http\Middleware\CustomerService;
use App\Http\Middleware\Operational;
use App\Http\Middleware\SalesPerson;
use App\Http\Middleware\SuperAdminMiddleware;
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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'superadmin' => SuperAdminMiddleware::class,
            'cs' => CustomerService::class,
            'sales' => SalesPerson::class,
            'operational' => Operational::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
