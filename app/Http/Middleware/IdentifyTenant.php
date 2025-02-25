<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
        // Get tenant from session or authenticated user
        $tenantId = session('tenant_id') ?? Auth::user()?->tenant_id;

        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
            
            if ($tenant) {
                // Set tenant in application context
                app()->instance('currentTenant', $tenant);
            }
        }

        return $next($request);
    }
}
