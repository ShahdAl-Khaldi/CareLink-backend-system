<?php

use App\Http\Middleware\CheckRole;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        then: function () {
            // تجميع كل المسارات تحت بادئة /api
            Route::prefix('api')->middleware('api')->group(function () {
                require base_path('routes/api/auth.php');
                require base_path('routes/api/admin.php');
                require base_path('routes/api/doctor.php');
                require base_path('routes/api/patient.php');
                require base_path('routes/api/reception.php');
                require base_path('routes/api/pharmacy.php');
                require base_path('routes/api/radiology.php');

            });
        },
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'checkRole' => CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'غير مصرح لك بالدخول'], 401);
            }
        });
    })->create();
