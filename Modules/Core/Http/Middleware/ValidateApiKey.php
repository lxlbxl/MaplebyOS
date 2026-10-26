<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-KEY');
        // Retrieve API key from config or env. defaulting to a value for dev if needed
        $validApiKey = config('core.api_key', env('API_KEY'));

        if (!$validApiKey) {
            // If no API key is configured, maybe allow or fail? 
            // Safest is to fail if this middleware is applied.
            // But for dev maybe we log a warning.
            // For now, let's strictly require it if it is set.
        }

        if (!$apiKey || $apiKey !== $validApiKey) {
            return response()->json(['message' => 'Unauthorized', 'error' => 'Invalid API Key'], 401);
        }

        return $next($request);
    }
}
