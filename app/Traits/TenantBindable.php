<?php

namespace App\Traits;

use App\Models\Tenant;

Trait TenantBindable
{
    public function bindTenant()
    {
        $tenant = Tenant::current();
        if ($tenant) {
            app()->instance('currentTenant', $tenant);
        }
    }
}
