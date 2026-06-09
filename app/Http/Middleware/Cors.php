<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Shared-hosting friendly CORS for browser / Expo web.
 * Mobile apps are not subject to CORS — only browsers need these headers.
 */
class Cors
{
    /** @var string[]|null */
    protected static $allowedOrigins;

    /**
     * @return string[]
     */
    protected static function allowedOrigins(): array
    {
        if (self::$allowedOrigins !== null) {
            return self::$allowedOrigins;
        }

        $fromConfig = config('cors.allowed_origins', []);
        $fromEnv = config('cors.env_extra_origins', []);

        self::$allowedOrigins = array_values(array_unique(array_merge($fromConfig, $fromEnv)));

        return self::$allowedOrigins;
    }

    public function handle(Request $request, Closure $next)
    {
        if (!$request->is('api/*')) {
            return $next($request);
        }

        $origin = (string) $request->headers->get('Origin', '');
        $allowOrigin = in_array($origin, self::allowedOrigins(), true) ? $origin : '';

        $headers = [
            'Access-Control-Allow-Origin' => $allowOrigin,
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, Accept, Origin, X-Requested-With',
            'Access-Control-Max-Age' => '86400',
            'Vary' => 'Origin',
        ];

        if ($request->getMethod() === 'OPTIONS') {
            return response('', 204, array_filter($headers));
        }

        $response = $next($request);

        foreach (array_filter($headers) as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
