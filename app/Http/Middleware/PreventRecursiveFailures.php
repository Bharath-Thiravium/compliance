<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class PreventRecursiveFailures
{
    private const FAILURE_KEY = 'recursive_failure_count';
    private const MAX_FAILURES = 2;

    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->getRequestFingerprint($request);
        $sessionKey = self::FAILURE_KEY . '_' . $key;
        
        $failureCount = session($sessionKey, 0);
        
        if ($failureCount > self::MAX_FAILURES) {
            Log::critical('Recursive failure loop detected', [
                'path' => $request->path(),
                'method' => $request->method(),
                'failures' => $failureCount,
            ]);
            
            return response('Service Temporarily Unavailable - Too Many Errors', 503);
        }

        try {
            $response = $next($request);
            
            if ($response->status() >= 500) {
                session([$sessionKey => $failureCount + 1]);
            } else {
                session([$sessionKey => 0]);
            }
            
            return $response;
        } catch (\Throwable $e) {
            session([$sessionKey => $failureCount + 1]);
            
            Log::critical('Request exception', [
                'path' => $request->path(),
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    private function getRequestFingerprint(Request $request): string
    {
        return hash('md5', $request->method() . ':' . $request->path());
    }
}
