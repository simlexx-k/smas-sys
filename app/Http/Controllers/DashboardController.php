<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->user()->role;
        
        switch ($role) {
            case 'landlord':
                return redirect()->route('landlord.dashboard');
            case 'tenant-admin':
                return redirect()->route('tenant.dashboard');
            case 'teacher':
                return redirect()->route('teacher.dashboard');
            default:
                return Inertia::render('Dashboard', [
                    'message' => 'Welcome to your dashboard!'
                ]);
        }
    }

    public function landlord(Request $request)
    {
        // Base queries
        $now = now();
        $tenantQuery = Tenant::query();
        $subscriptionQuery = Subscription::query();

        // Active subscription conditions
        $activeSubscriptionConditions = function ($query) use ($now) {
            $query->where('status', Subscription::STATUS_ACTIVE)
                ->where(function ($q) use ($now) {
                    $q->whereNull('ends_at')
                        ->orWhere('ends_at', '>', $now);
                })
                ->whereNull('cancels_at');
        };

        // First get subscription counts
        $activeSubscriptions = $subscriptionQuery->tap($activeSubscriptionConditions)->count();

        // Calculate basic stats
        $stats = [
            'total_tenants' => $tenantQuery->count(),
            'active_tenants' => $tenantQuery->whereHas('subscription', $activeSubscriptionConditions)->count(),
            'tenants_without_subscription' => $tenantQuery->doesntHave('subscription')->count(),
            'active_subscriptions' => $activeSubscriptions,

            // Add recent tenants data
            'recent_tenants' => [
                'data' => Tenant::with(['subscription'])
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(function ($tenant) {
                        return [
                            'id' => $tenant->id,
                            'name' => $tenant->name,
                            'email' => $tenant->email,
                            'created_at' => $tenant->created_at,
                            'subscription_status' => $tenant->subscription?->status ?? 'no_subscription'
                        ];
                    }),
                'links' => [] // If you're not using pagination for recent tenants
            ],

            // Revenue metrics
            'revenue_metrics' => [
                'monthly_recurring_revenue' => $subscriptionQuery
                    ->where('status', Subscription::STATUS_ACTIVE)
                    ->whereNull('cancels_at')
                    ->sum('price'),
                'average_revenue_per_tenant' => function () use ($subscriptionQuery) {
                    $activeCount = $subscriptionQuery
                        ->where('status', Subscription::STATUS_ACTIVE)
                        ->whereNull('cancels_at')
                        ->count();
                    $totalRevenue = $subscriptionQuery
                        ->where('status', Subscription::STATUS_ACTIVE)
                        ->whereNull('cancels_at')
                        ->sum('price');
                    return $activeCount > 0 ? $totalRevenue / $activeCount : 0;
                },
                'projected_annual_revenue' => function () use ($subscriptionQuery) {
                    return $subscriptionQuery
                        ->where('status', Subscription::STATUS_ACTIVE)
                        ->whereNull('cancels_at')
                        ->sum('price') * 12;
                }
            ],

            // Status breakdown
            'status_breakdown' => [
                'active' => $activeSubscriptions,
                'trial' => $subscriptionQuery
                    ->where('status', Subscription::STATUS_ACTIVE)
                    ->where('trial_ends_at', '>', $now)
                    ->count(),
                'expiring_soon' => $subscriptionQuery
                    ->where('status', Subscription::STATUS_ACTIVE)
                    ->where('ends_at', '>', $now)
                    ->where('ends_at', '<=', $now->copy()->addDays(30))
                    ->count(),
                'expired' => $subscriptionQuery->where('status', Subscription::STATUS_EXPIRED)->count(),
                'canceled' => $subscriptionQuery
                    ->where('status', Subscription::STATUS_CANCELED)
                    ->orWhere('cancels_at', '!=', null)
                    ->count(),
                'canceling' => $subscriptionQuery
                    ->whereNotNull('cancels_at')
                    ->where('ends_at', '>', $now)
                    ->count()
            ],

            // Subscription health metrics
            'subscription_health' => [
                'active_rate' => function () use ($tenantQuery, $activeSubscriptionConditions) {
                    $total = $tenantQuery->count();
                    if ($total === 0) return 0;
                    $active = $tenantQuery->whereHas('subscription', $activeSubscriptionConditions)->count();
                    return ($active / $total) * 100;
                },
                'churn_rate' => function () {
                    return $this->calculateChurnRate();
                },
                'renewal_rate' => function () {
                    return $this->calculateRenewalRate();
                },
                'trial_conversion_rate' => function () use ($subscriptionQuery, $now) {
                    $completedTrials = $subscriptionQuery
                        ->where('trial_ends_at', '<=', $now)
                        ->count();
                    if ($completedTrials === 0) return 0;
                    $convertedTrials = $subscriptionQuery
                        ->where('trial_ends_at', '<=', $now)
                        ->where('status', Subscription::STATUS_ACTIVE)
                        ->count();
                    return ($convertedTrials / $completedTrials) * 100;
                }
            ],

            // Time metrics
            'time_metrics' => [
                'average_time_to_conversion' => $subscriptionQuery
                    ->whereNotNull('trial_ends_at')
                    ->whereNotNull('created_at')
                    ->selectRaw('AVG(EXTRACT(EPOCH FROM (trial_ends_at - created_at))/86400) as avg_days')
                    ->value('avg_days') ?? 0,
                'average_subscription_age' => $subscriptionQuery
                    ->where('status', Subscription::STATUS_ACTIVE)
                    ->whereNotNull('created_at')
                    ->selectRaw('AVG(EXTRACT(EPOCH FROM (NOW() - created_at))/86400) as avg_days')
                    ->value('avg_days') ?? 0
            ],

            // Add renewal tracking metrics
            'renewal_tracking' => [
                'last_30_days' => $subscriptionQuery
                    ->where('status', Subscription::STATUS_ACTIVE)
                    ->where('created_at', '>=', $now->copy()->subDays(30))
                    ->count(),
                'last_90_days' => $subscriptionQuery
                    ->where('status', Subscription::STATUS_ACTIVE)
                    ->where('created_at', '>=', $now->copy()->subDays(90))
                    ->count(),
                'average_renewal_interval' => function () use ($subscriptionQuery) {
                    // Calculate average time between subscription start and end
                    $avgDays = $subscriptionQuery
                        ->where('status', Subscription::STATUS_ACTIVE)
                        ->whereNotNull('ends_at')
                        ->selectRaw('AVG(EXTRACT(EPOCH FROM (ends_at - created_at))/86400) as avg_days')
                        ->value('avg_days');
                    
                    return $avgDays ?? 0;
                }
            ]
        ];

        // Calculate health metrics that depend on other stats
        $stats['subscription_health'] = array_map(
            fn($value) => is_callable($value) ? $value() : $value,
            $stats['subscription_health']
        );

        // Calculate revenue metrics that are functions
        $stats['revenue_metrics'] = array_map(
            fn($value) => is_callable($value) ? $value() : $value,
            $stats['revenue_metrics']
        );

        // Make sure to process any callable values
        $stats['renewal_tracking'] = array_map(
            fn($value) => is_callable($value) ? $value() : $value,
            $stats['renewal_tracking']
        );

        return Inertia::render('Landlord/Dashboard', [
            'stats' => $stats,
            'systemStatus' => $this->getSystemStatus(),
            'activities' => Activity::with(['user', 'tenant'])
                ->latest()
                ->paginate(10),
            'filters' => $request->only(['search', 'status'])
        ]);
    }

    public function tenant(Request $request)
    {
        // Tenant specific dashboard logic
        return Inertia::render('Tenant/Dashboard', [
            'tenant' => $request->user()->tenant
            // ... other tenant specific data
        ]);
    }

    private function getSystemStatus(): array
    {
        return [
            'database' => $this->checkDatabaseConnection(),
            'storage' => $this->checkStorageWritable(),
            'cache' => $this->checkCacheConnection(),
            'queue' => $this->checkQueueConnection(),
        ];
    }

    private function checkDatabaseConnection(): string
    {
        try {
            \DB::connection()->getPdo();
            return 'healthy';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    private function checkStorageWritable(): string
    {
        return is_writable(storage_path()) ? 'healthy' : 'error';
    }

    private function checkCacheConnection(): string
    {
        try {
            \Cache::store()->get('health-check-' . now()->timestamp);
            return 'healthy';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    private function checkQueueConnection(): string
    {
        try {
            $connection = config('queue.default');
            if ($connection === 'sync') {
                return 'healthy';
            }
            \Queue::connection($connection)->getConnectionName();
            return 'healthy';
        } catch (\Exception $e) {
            return 'error';
        }
    }

    private function calculateRenewalRate(): float
    {
        // For now, consider a renewal when a subscription is active and older than 30 days
        $totalRenewals = Subscription::where('status', 'active')
            ->where('created_at', '<=', now()->subDays(30))
            ->count();

        $totalEligible = Subscription::where('created_at', '<=', now()->subDays(30))
            ->count();

        return $totalEligible > 0 ? ($totalRenewals / $totalEligible) * 100 : 0;
    }

    private function calculateChurnRate(): float
    {
        $thirtyDaysAgo = now()->subDays(30);
        
        // Count subscriptions that expired in the last 30 days
        $canceledSubscriptions = Subscription::where('status', 'expired')
            ->where('ends_at', '>=', $thirtyDaysAgo)
            ->where('ends_at', '<=', now())
            ->count();

        // Total subscriptions that existed 30 days ago
        $totalSubscriptions = Subscription::where('created_at', '<=', $thirtyDaysAgo)
            ->count();

        return $totalSubscriptions > 0 ? ($canceledSubscriptions / $totalSubscriptions) * 100 : 0;
    }
} 