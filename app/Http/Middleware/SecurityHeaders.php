<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds OWASP recommended security headers to every HTTP response.
 *
 * Protects against clickjacking, MIME sniffing, XSS and unwanted
 * browser feature access. Registered globally in bootstrap/app.php
 * so every response is covered automatically.
 */
class SecurityHeaders
{
    /**
     * Attach security headers to the response before it reaches the browser.
     * Headers are added after the request is handled so they apply to every response type.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
