<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Redirect non-HTTPS requests to HTTPS (primarily to prevent secure cookies
     * from being dropped when users first hit the site over HTTP).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldRedirectToHttps($request)) {
            $httpsUrl = preg_replace('/^http:/i', 'https:', $request->getUri()) ?? $request->getUri();

            return new RedirectResponse($httpsUrl, 301);
        }

        return $next($request);
    }

    private function shouldRedirectToHttps(Request $request): bool
    {
        if ($request->secure()) {
            return false;
        }

        if (app()->environment('local', 'testing')) {
            return false;
        }

        $env = env('FORCE_HTTPS');
        if ($env !== null) {
            return filter_var($env, FILTER_VALIDATE_BOOLEAN);
        }

        return str_starts_with((string) config('app.url'), 'https://');
    }
}
