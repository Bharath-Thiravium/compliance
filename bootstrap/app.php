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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'subscription.full' => \App\Http\Middleware\EnforceFullSubscription::class,
            'auth'              => \App\Http\Middleware\Authenticate::class,
            'super.admin'       => \App\Http\Middleware\SuperAdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Return JSON only for genuine AJAX/API requests (fetch with Accept: application/json
        // or X-Requested-With header). Never intercept browser page navigations.
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if (! $request->expectsJson()) {
                return null; // let Laravel render the normal HTML error page
            }

            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                    'errors'  => $e->errors(),
                ], 422);
            }

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage() ?: 'An unexpected error occurred.',
            ], $status >= 400 ? $status : 500);
        });
    })->create();
