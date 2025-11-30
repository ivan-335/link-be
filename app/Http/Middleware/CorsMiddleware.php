<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->headers->get('Origin');

        // Add all dev origins you actually use here
        $allowedOrigins = [
            'http://localhost:5173',
            'http://127.0.0.1:5173',
            'https://localhost:5173',
            'https://127.0.0.1:5173',
        ];

        // Handle preflight right away
        if ($request->getMethod() === 'OPTIONS') {
            $response = response('', 204);
            return $this->addCorsHeaders($response, $origin, $allowedOrigins);
        }

        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        return $this->addCorsHeaders($response, $origin, $allowedOrigins);
    }

    /**
     * Add the CORS headers if origin is allowed.
     */
    protected function addCorsHeaders(Response $response, ?string $origin, array $allowedOrigins): Response
    {
        if ($origin && in_array($origin, $allowedOrigins, true)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set(
                'Access-Control-Allow-Headers',
                'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, Accept, Origin'
            );
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }
}
