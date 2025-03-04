<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\Plan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    public function index()
    {
        // First check if the plan exists
        $planExists = Plan::find(2);
        Log::info('Checking plan existence', [
            'plan_id_2_exists' => $planExists !== null,
            'plan_data' => $planExists?->toArray()
        ]);

        $subscriptions = Subscription::with(['tenant', 'plan'])
            ->latest()
            ->paginate(10);

        Log::info('Fetched subscriptions', [
            'count' => $subscriptions->count(),
            'total' => $subscriptions->total(),
            'sample_subscription' => $subscriptions->first()?->toArray(),
            'sample_subscription_plan_id' => $subscriptions->first()?->plan_id,
            'sample_subscription_plan' => $subscriptions->first()?->plan?->toArray()
        ]);

        return Inertia::render('Admin/Subscriptions/Index', [
            'subscriptions' => $subscriptions
        ]);
    }

    public function create()
    {
        $tenants = Tenant::select('id', 'name')->get();
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        Log::info('Loading subscription create form', [
            'tenants_count' => $tenants->count(),
            'plans_count' => $plans->count()
        ]);

        return Inertia::render('Admin/Subscriptions/Create', [
            'tenants' => $tenants,
            'plans' => $plans
        ]);
    }

    public function store(Request $request)
    {
        Log::info('Attempting to create subscription', [
            'request_data' => $request->all()
        ]);

        try {
            $validated = $request->validate([
                'tenant_id' => 'required|exists:tenants,id',
                'plan_id' => 'required|exists:plans,id',
                'starts_at' => 'required|date',
                'ends_at' => 'nullable|date|after:starts_at',
                'trial_ends_at' => 'nullable|date',
                'price' => 'required|numeric|min:0',
                'features' => 'nullable|array',
                'payment_method' => 'required|string',
            ]);

            Log::info('Validation passed', ['validated_data' => $validated]);

            $tenant = Tenant::findOrFail($validated['tenant_id']);
            $plan = Plan::findOrFail($validated['plan_id']);

            Log::info('Found plan before subscription creation', [
                'plan_id' => $plan->id,
                'plan_data' => $plan->toArray()
            ]);

            $subscription = $tenant->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => $validated['starts_at'],
                'ends_at' => $validated['ends_at'],
                'trial_ends_at' => $validated['trial_ends_at'],
                'price' => $validated['price'],
                'features' => $validated['features'],
                'payment_method' => $validated['payment_method'],
            ]);

            Log::info('Created subscription with plan', [
                'subscription_id' => $subscription->id,
                'plan_id' => $subscription->plan_id,
                'plan_exists' => Plan::where('id', $subscription->plan_id)->exists(),
                'subscription_plan' => $subscription->plan?->toArray()
            ]);

            $subscription->load('plan');

            return redirect()
                ->route('admin.subscriptions.show', $subscription)
                ->with('success', 'Subscription created successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to create subscription', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withErrors(['error' => 'Failed to create subscription: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show(Subscription $subscription)
    {
        Log::info('Viewing subscription details', [
            'subscription_id' => $subscription->id,
            'subscription_data' => $subscription->toArray()
        ]);

        $subscription->load(['tenant', 'plan']);

        Log::info('Loaded subscription relationships', [
            'has_tenant' => $subscription->tenant !== null,
            'has_plan' => $subscription->plan !== null,
            'plan_data' => $subscription->plan?->toArray()
        ]);

        return Inertia::render('Admin/Subscriptions/Show', [
            'subscription' => $subscription,
            'availablePlans' => Plan::where('is_active', true)
                ->orderBy('sort_order')
                ->get()
        ]);
    }

    private function getAvailablePlans(): array
    {
        return [
            [
                'id' => 'basic',
                'name' => 'Basic',
                'price' => 99.99,
                'features' => [
                    'Up to 500 students',
                    'Basic reporting',
                    'Email support',
                ],
            ],
            [
                'id' => 'pro',
                'name' => 'Professional',
                'price' => 199.99,
                'features' => [
                    'Unlimited students',
                    'Advanced reporting',
                    'Priority support',
                    'Custom branding',
                ],
            ],
            [
                'id' => 'enterprise',
                'name' => 'Enterprise',
                'price' => 499.99,
                'features' => [
                    'All Pro features',
                    'API access',
                    'Dedicated support',
                    'Custom development',
                ],
            ],
        ];
    }
} 