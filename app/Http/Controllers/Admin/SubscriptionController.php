<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::with('tenant')
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/Subscriptions/Index', [
            'subscriptions' => $subscriptions
        ]);
    }

    public function create(Tenant $tenant)
    {
        return Inertia::render('Admin/Subscriptions/Create', [
            'tenant' => $tenant,
            'plans' => $this->getAvailablePlans()
        ]);
    }

    public function store(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'plan' => 'required|string',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'trial_ends_at' => 'nullable|date',
            'price' => 'required|numeric|min:0',
            'features' => 'nullable|array',
            'payment_method' => 'nullable|string',
        ]);

        $subscription = $tenant->subscriptions()->create([
            ...$validated,
            'status' => Subscription::STATUS_ACTIVE,
            'next_payment_at' => $validated['ends_at'] ?? null,
        ]);

        return redirect()->route('admin.subscriptions.show', $subscription)
            ->with('success', 'Subscription created successfully.');
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