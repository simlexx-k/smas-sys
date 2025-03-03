<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        $requestedTenantId = $request->query('tenant_id');

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        if ($user->tenant_id != $requestedTenantId && !$user->hasRole('landlord')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
} 