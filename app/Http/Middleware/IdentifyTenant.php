<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
        // Skip tenant identification for landlords
        if (Auth::check() && Auth::user()->hasRole(User::ROLE_LANDLORD)) {
            return $next($request);
        }

        // Proceed with tenant identification for other roles
        if (Auth::check()) {
            $tenantId = session('tenant_id') ?? Auth::user()->tenant_id;

            if ($tenantId) {
                $tenant = Tenant::find($tenantId);
                
                if ($tenant) {
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
