<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    protected $levels = [];
    protected $dontReport = [];
    protected $dontFlash = ['current_password', 'password', 'password_confirmation'];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            Log::critical('UNHANDLED EXCEPTION', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        });
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof \Illuminate\Encryption\MissingAppKeyException) {
            Log::critical('APP KEY MISSING');
            return response('Service Unavailable', 503);
        }

        if ($e instanceof \PDOException) {
            Log::critical('DATABASE FAILED: ' . $e->getMessage());
            return response('Service Unavailable', 503);
        }

        if (!$this->isHttpException($e)) {
            Log::critical('CONTROLLED UNHANDLED EXCEPTION RESPONSE', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'The request failed safely. Please check the application logs for details.',
                ], 500);
            }

            return response()->view('errors.template-failed', [
                'message' => 'The request failed safely. Please check the application logs for details.',
            ], 500);
        }

        return parent::render($request, $e);
    }
}
