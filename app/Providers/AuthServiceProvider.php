<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Attendance;
use App\Policies\AttendancePolicy;
use App\Models\Invoice;
use App\Policies\InvoicePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Attendance::class => AttendancePolicy::class,
        Invoice::class => InvoicePolicy::class,
        // ... other policies ...
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define gates here if needed
        Gate::define('access-tenant', function ($user) {
            return $user->role === 'tenant-admin';
        });

        Gate::define('access-landlord', function ($user) {
            return $user->role === 'landlord';
        });
    }
} 