<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get all active subscriptions
        $activeSubscriptions = Subscription::where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            });

        // Calculate total monthly revenue from active subscriptions
        $monthlyRevenue = (float) $activeSubscriptions->sum('price') ?? 0;

        $subscriptionStats = [
            'active' => $activeSubscriptions->count(),
            'trial' => Subscription::where('status', 'trial')
                ->where('trial_ends_at', '>', now())
                ->count(),
            'expired' => Subscription::where(function ($query) {
                $query->where('status', 'expired')
                    ->orWhere(function ($q) {
                        $q->where('ends_at', '<=', now())
                            ->whereNotNull('ends_at');
                    });
            })->count(),
            'expiring_soon' => Subscription::where('status', 'active')
                ->where('ends_at', '<=', now()->addDays(30))
                ->where('ends_at', '>', now())
                ->count(),
            'monthly_revenue' => $monthlyRevenue,
            'total' => Subscription::count(),
        ];

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_tenants' => Tenant::count(),
                'active_tenants' => Tenant::where('is_active', true)->count(),
                'subscription_stats' => $subscriptionStats,
                'recent_tenants' => Tenant::latest()
                    ->with('subscription')
                    ->paginate(10),
            ],
            'systemStatus' => $this->getSystemStatus(),
            'activities' => Activity::with(['user', 'tenant'])
                ->latest()
                ->paginate(10),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    private function getSystemStatus(): array
    {
        return [
            'database' => $this->checkDatabaseConnection() ? 'healthy' : 'error',
            'storage' => $this->checkStorageWritable() ? 'healthy' : 'error',
            'cache' => $this->checkCacheConnection() ? 'healthy' : 'error',
            'queue' => $this->checkQueueConnection() ? 'healthy' : 'error',
        ];
    }
} 