<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ExternalApiIpAllowlist
{
    public function handle(Request $request, Closure $next)
    {
        $allowedIps = config('external_api.allowed_ips', []);
        $clientIp   = $request->ip();

        if (empty($allowedIps)) {
            Log::channel('external_system')->warning('External API blocked - allowlist not configured', [
                'ip'   => $clientIp,
                'path' => $request->path(),
            ]);

            return response()->json([
                'message' => 'External API access is not configured.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (!in_array($clientIp, $allowedIps, true)) {
            Log::channel('external_system')->warning('External API blocked - IP not allowed', [
                'ip'          => $clientIp,
                'allowed_ips' => $allowedIps,
                'path'        => $request->path(),
            ]);

            return response()->json([
                'message' => 'Forbidden.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
