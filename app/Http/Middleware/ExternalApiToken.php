<?php

namespace App\Http\Middleware;

use App\Models\ExternalApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ExternalApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-API-Token') ?? $request->bearerToken();
        
        // Log access attempt
        Log::channel('external_api')->info('External API access attempt', [
            'ip' => $request->ip(),
            'endpoint' => $request->path(),
            'token_provided' => !empty($token),
            'timestamp' => now(),
        ]);

        if (empty($token)) {
            Log::channel('external_api')->warning('External API access denied: No token provided', [
                'ip' => $request->ip(),
                'endpoint' => $request->path(),
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication token required',
            ], 401);
        }

        // Find the API key in database
        $apiKey = ExternalApiKey::where('api_key', $token)->first();

        if (!$apiKey) {
            Log::channel('external_api')->warning('External API access denied: Invalid token', [
                'ip' => $request->ip(),
                'endpoint' => $request->path(),
                'token_prefix' => substr($token, 0, 8) . '...',
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid authentication token',
            ], 403);
        }

        // Check if key is valid (active and not expired)
        if (!$apiKey->isValid()) {
            Log::channel('external_api')->warning('External API access denied: Inactive or expired token', [
                'ip' => $request->ip(),
                'endpoint' => $request->path(),
                'client_name' => $apiKey->name,
                'is_active' => $apiKey->is_active,
                'expires_at' => $apiKey->expires_at,
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'API key is inactive or expired',
            ], 403);
        }

        // Check IP restriction if configured
        if (!$apiKey->isIpAllowed($request->ip())) {
            Log::channel('external_api')->warning('External API access denied: IP not allowed', [
                'ip' => $request->ip(),
                'endpoint' => $request->path(),
                'client_name' => $apiKey->name,
                'allowed_ips' => $apiKey->allowed_ips,
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Access from this IP address is not allowed',
            ], 403);
        }

        // Check domain restriction if configured
        $origin = $request->header('Origin') ?? $request->header('Referer');
        if (!$apiKey->isDomainAllowed($origin)) {
            Log::channel('external_api')->warning('External API access denied: Domain not allowed', [
                'ip' => $request->ip(),
                'endpoint' => $request->path(),
                'client_name' => $apiKey->name,
                'origin' => $origin,
                'allowed_domains' => $apiKey->allowed_domains,
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Access from this domain is not allowed',
            ], 403);
        }

        // Mark the key as used
        $apiKey->markAsUsed();

        // Log successful authentication
        Log::channel('external_api')->info('External API access granted', [
            'ip' => $request->ip(),
            'endpoint' => $request->path(),
            'client_name' => $apiKey->name,
            'client_id' => $apiKey->id,
            'company_id' => $apiKey->company_id,
            'origin' => $origin,
        ]);

        // Add API key info to request for use in controller if needed
        $request->attributes->add(['external_api_client' => $apiKey]);

        return $next($request);
    }
}
