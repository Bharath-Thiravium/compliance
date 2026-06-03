<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->validateCsrfTokens(except: [
            'api/*',
            '_ops/*',
            'compliance/batch/create',
            'compliance/batch/create-minimal',
            'compliance/manual-batch/create',
        ]);
        $middleware->alias([
            'subscription.full' => \App\Http\Middleware\EnforceFullSubscription::class,
            'auth'              => \App\Http\Middleware\Authenticate::class,
            'super.admin'       => \App\Http\Middleware\SuperAdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            // Log all exceptions at critical level
            Log::critical('EXCEPTION RENDERED', [
                'class' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Handle encryption key missing
            if ($e instanceof \Illuminate\Encryption\MissingAppKeyException) {
                Log::critical('MISSING APP ENCRYPTION KEY');
                return response('Service Unavailable: Application encryption key not set', 503);
            }

            // Handle database connection failures
            if ($e instanceof \PDOException || $e instanceof \Illuminate\Database\QueryException) {
                Log::critical('DATABASE CONNECTION FAILED', ['message' => $e->getMessage()]);
                return response('Service Unavailable: Database connection failed', 503);
            }

            // Handle vite manifest missing
            if (strpos($e->getMessage(), 'Vite manifest') !== false) {
                Log::warning('Vite manifest not found - assets not compiled');
                return response('Service Unavailable: Assets not compiled', 503);
            }

            if (!$request->expectsJson()) {
                return null;
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
