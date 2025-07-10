<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get API key from request headers
        $requestApiKey = $request->header('apiKey');

        if (!$requestApiKey) {
            return response()->json(['message' => 'API Key is required'], 401);
        }

        // Define your API key (store in .env for security)
        $apiKey = env('API_SECRET');
        if ($requestApiKey !== $apiKey) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
