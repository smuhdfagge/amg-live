<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Browser-side protection against cross-site scripting (XSS) and clickjacking.
 *
 * The Content Security Policy only lets the browser run JavaScript from this
 * site's own files. Even if harmful markup ever reached a page, an injected
 * <script>, an onclick="..." attribute or a javascript: link would not run.
 * The site has no inline scripts, which is what makes this strict policy work.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Files we send ourselves (downloads, uploads) set their own, stricter policy.
        if ($response->headers->has('Content-Security-Policy')) {
            return $response;
        }

        $policy = implode('; ', [
            "default-src 'self'",
            "script-src 'self'",
            "style-src 'self' 'unsafe-inline'",              // style="" attributes in the templates
            "img-src 'self' data: https://i.ytimg.com",      // YouTube video covers
            "media-src 'self' https:",                       // audio and video linked from other sites
            "font-src 'self'",
            'frame-src https://www.youtube-nocookie.com https://player.vimeo.com https://maps.google.com https://www.google.com',
            "connect-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            $request->isSecure() ? 'upgrade-insecure-requests' : null,
        ]);

        $response->headers->set('Content-Security-Policy', trim($policy, '; '));
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        return $response;
    }
}
