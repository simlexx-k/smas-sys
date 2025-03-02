<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
        // Only check tenant for authenticated users
        if (Auth::check()) {
            $tenantId = session('tenant_id') ?? Auth::user()->tenant_id;

            \Log::info('Tenant ID:', ['tenantId' => $tenantId]);

            if ($tenantId) {
                $tenant = Tenant::find($tenantId);
                
                \Log::info('Tenant found:', ['tenant' => $tenant]);

                if ($tenant) {
                    // Set tenant in application context
                    app()->instance('currentTenant', $tenant);
                } else {
                    return Inertia::render('Error', [
                        'error' => 'Tenant not found',
                    ]);
                }
            } else {
                return Inertia::render('Error', [
                    'error' => 'Tenant ID not set',
                ]);
            }
        }

        return $next($request);
    }
}
