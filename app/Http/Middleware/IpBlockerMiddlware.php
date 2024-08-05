<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\IpWhitelist;
use Illuminate\Support\Facades\Log;

class IpBlockerMiddlware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info("Petición recibida desde la IP: " . $request->ip());
        if(!IpWhitelist::isWhitelisted($request->ip())) {
            abort(403);
        }
        return $next($request);
    }
}
