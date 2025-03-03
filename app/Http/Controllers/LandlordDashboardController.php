<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Tenant;
use App\Services\SystemHealthService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LandlordDashboardController extends Controller
{
    public function __construct(
        private SystemHealthService $healthService
    ) {}

    public function index(Request $request): Response
    {
        try {
            $query = Tenant::query();

            // Apply filters
            if ($request->has('search')) {
                $query->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $tenants = $query->latest()
                ->paginate(5)
                ->withQueryString();

            // Get subscription statistics
            $subscriptionStats = [
                'active' => Tenant::where('status', 'active')
                    ->where(function ($query) {
                        $query->whereNull('subscription_ends_at')
                            ->orWhere('subscription_ends_at', '>', now());
                    })->count(),
                'expiring_soon' => Tenant::where('status', 'active')
                    ->where('subscription_ends_at', '<=', now()->addDays(30))
                    ->where('subscription_ends_at', '>', now())
                    ->count(),
                'expired' => Tenant::where('subscription_ends_at', '<=', now())->count(),
                'trial' => Tenant::where('subscription_plan', 'trial')->count(),
            ];

            $activities = Activity::with(['user', 'subject'])
                ->latest()
                ->paginate(10)
                ->withQueryString();

            return Inertia::render('Dashboard', [
                'stats' => [
                    'total_tenants' => Tenant::count(),
                    'active_tenants' => Tenant::where('status', 'active')->count(),
                    'recent_tenants' => $tenants,
                    'subscription_stats' => $subscriptionStats,
                ],
                'systemStatus' => app(SystemHealthService::class)->checkAll(),
                'activities' => $activities,
                'filters' => $request->only(['search', 'status']),
            ]);
        } catch (\Exception $e) {
            report($e); // Log the error
            return Inertia::render('Dashboard', [
                'error' => 'Failed to load dashboard data',
            ])->with('flash', [
                'type' => 'error',
                'message' => 'There was an error loading the dashboard.',
            ]);
        }
    }
} 