<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\AdminPasswordReset;
use App\Models\Activity;
use App\Exports\DeletedTenantsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Plan;

class LandlordTenantController extends Controller
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function index(Request $request)
    {
        $tenants = Tenant::with(['subscription', 'domains'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->through(fn ($tenant) => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'email' => $tenant->email,
                'logo_url' => $tenant->logo_url,
                'created_at' => $tenant->created_at,
                'is_active' => $tenant->status === 'active',
                'subscription' => $tenant->subscription ? [
                    'status' => $tenant->subscription->status,
                    'ends_at' => $tenant->subscription->ends_at,
                ] : null,
            ])
            ->withQueryString();

        $trashedCount = Tenant::onlyTrashed()->count();

        return Inertia::render('Admin/Tenants/Index', [
            'schools' => [
                'data' => $tenants->items(),
                'links' => $tenants->links()
            ],
            'filters' => $request->only(['search', 'status']),
            'trashedCount' => $trashedCount
        ]);
    }

    public function create()
    {
        $data = [
            'tenant' => null,
            'schoolTypes' => [
                'primary' => 'Primary School',
                'secondary' => 'Secondary School',
                'college' => 'College',
                'university' => 'University',
                'other' => 'Other'
            ],
            'subscriptionPlans' => Plan::where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->mapWithKeys(fn($plan) => [$plan->slug => $plan->name])
                ->toArray(),
            'statuses' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
                'suspended' => 'Suspended'
            ],
        ];

        // Add debug logging
        \Log::info('Create Tenant Form Data:', $data);

        return Inertia::render('Admin/Tenants/Create', $data);
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['subscription', 'admin']);
        
        return Inertia::render('Admin/Tenants/Show', [
            'tenant' => $tenant,
            'stats' => [
                'usage' => $this->tenantService->getUsageStats($tenant),
                'tenant' => $this->tenantService->getTenantStats($tenant)
            ]
        ]);
    }

    public function store(Request $request)
    {
        \Log::info('Tenant creation started', [
            'request_data' => $request->all(),
            'available_plans' => Plan::where('is_active', true)
                ->pluck('slug')
                ->toArray()
        ]);

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'domain' => 'required|string|max:255|unique:tenants',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'logo' => 'nullable|image|max:1024',
                'status' => 'required|string|in:active,inactive,suspended',
                'school_type' => 'required|string',
                'subscription_plan' => ['required', 'string', Rule::exists('plans', 'slug')->where('is_active', true)],
                'admin_name' => 'required|string|max:255',
                'admin_email' => 'required|email|max:255',
                'admin_password' => 'required|string|min:8',
                'subscription_starts_at' => 'required|date',
                'trial_ends_at' => 'required|date',
            ]);

            \Log::info('Validation passed', ['validated_data' => $validated]);

            DB::beginTransaction();

            // Create the tenant
            $tenant = Tenant::create([
                'name' => $validated['name'],
                'domain' => $validated['domain'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'status' => $validated['status'],
                'school_type' => $validated['school_type'],
                'subscription_plan' => $validated['subscription_plan'],
            ]);

            \Log::info('Tenant created', ['tenant_id' => $tenant->id]);

            // Handle logo upload if present
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('tenant-logos', 'public');
                $tenant->logo_url = Storage::url($path);
                $tenant->save();
                \Log::info('Logo uploaded', ['path' => $path]);
            }

            // Create admin user
            $admin = User::create([
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'password' => Hash::make($validated['admin_password']),
                'tenant_id' => $tenant->id,
                'role' => 'admin',
            ]);

            \Log::info('Admin user created', ['admin_id' => $admin->id]);

            // Create subscription
            $subscription = $tenant->subscriptions()->create([
                'status' => 'active',
                'starts_at' => $validated['subscription_starts_at'],
                'ends_at' => Carbon::parse($validated['subscription_starts_at'])->addMonth(),
                'trial_ends_at' => $validated['trial_ends_at'],
                'price' => $this->getSubscriptionPrice($validated['subscription_plan']),
                'features' => json_encode($this->getSubscriptionFeatures($validated['subscription_plan'])),
            ]);

            \Log::info('Subscription created', ['subscription_id' => $subscription->id]);

            DB::commit();
            \Log::info('Tenant creation completed successfully');

            return redirect()
                ->route('admin.tenants.show', $tenant)
                ->with('success', 'School created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Tenant creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Failed to create school: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|unique:tenants,domain,' . $tenant->id,
            'status' => 'required|string|in:active,inactive,suspended',
            'subscription_plan' => 'required|string|in:basic,premium,enterprise',
            'school_type' => 'required|string'
        ]);

        $tenant->update($validated);
        
        return redirect()
            ->route('admin.tenants.show', $tenant)
            ->with('success', 'School updated successfully');
    }

    public function destroy(Tenant $tenant)
    {
        try {
            DB::beginTransaction();

            // Log the deletion activity before deleting the tenant
            Activity::log(
                'tenant',
                'delete',
                "Deleted school: {$tenant->name}",
                $tenant
            );

            // Delete the tenant
            $tenant->delete();

            DB::commit();

            return redirect()
                ->route('admin.tenants.index')
                ->with('success', 'School has been successfully deleted.');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e); // Log the error

            return redirect()
                ->back()
                ->with('error', 'Failed to delete school. Please try again.');
        }
    }

    // Domain Management
    public function storeDomain(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:255|unique:domains,domain',
            'is_primary' => 'boolean',
            'verification_method' => 'required|in:dns,file'
        ]);

        $domain = $this->tenantService->createDomain($tenant, $validated);

        return back()->with('success', 'Domain added successfully');
    }

    public function setPrimaryDomain(Tenant $tenant, Domain $domain)
    {
        $this->tenantService->setPrimaryDomain($tenant, $domain);

        return back()->with('success', 'Primary domain updated');
    }

    // Stats endpoints
    public function getUsageStats(Tenant $tenant)
    {
        return response()->json(
            $this->tenantService->getUsageStats($tenant)
        );
    }

    public function getClassStats(Tenant $tenant)
    {
        return response()->json(
            $this->tenantService->getClassStats($tenant)
        );
    }

    public function getStudentStats(Tenant $tenant)
    {
        return response()->json(
            $this->tenantService->getStudentStats($tenant)
        );
    }

    public function edit(Tenant $tenant)
    {
        return Inertia::render('Admin/Tenants/Edit', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'domain' => $tenant->domain,
                'email' => $tenant->email,
                'phone' => $tenant->phone,
                'address' => $tenant->address,
                'logo_url' => $tenant->logo_url,
                'status' => $tenant->status,
                'school_type' => $tenant->school_type,
                'subscription_plan' => $tenant->subscription_plan,
                'subscription' => $tenant->subscription ? [
                    'id' => $tenant->subscription->id,
                    'status' => $tenant->subscription->status,
                    'starts_at' => $tenant->subscription->starts_at,
                    'ends_at' => $tenant->subscription->ends_at,
                    'trial_ends_at' => $tenant->subscription->trial_ends_at,
                    'price' => $tenant->subscription->price,
                    'features' => $tenant->subscription->features,
                    'payment_method' => $tenant->subscription->payment_method,
                    'last_payment_at' => $tenant->subscription->last_payment_at,
                    'next_payment_at' => $tenant->subscription->next_payment_at,
                ] : null,
                'admin' => $tenant->admin ? [
                    'id' => $tenant->admin->id,
                    'name' => $tenant->admin->name,
                    'email' => $tenant->admin->email,
                    'email_verified_at' => $tenant->admin->email_verified_at,
                    'created_at' => $tenant->admin->created_at,
                    'updated_at' => $tenant->admin->updated_at,
                ] : null,
            ],
            'schoolTypes' => [
                'primary' => 'Primary School',
                'secondary' => 'Secondary School',
                'college' => 'College',
                'university' => 'University',
                'other' => 'Other'
            ],
            'subscriptionPlans' => [
                'basic' => 'Basic Plan',
                'premium' => 'Premium Plan',
                'enterprise' => 'Enterprise Plan'
            ],
            'statuses' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
                'suspended' => 'Suspended'
            ],
        ]);
    }

    public function impersonate(Tenant $tenant)
    {
        if (!$tenant->admin) {
            return back()->with('error', 'No administrator found for this school.');
        }

        // Store the current admin's ID in the session
        session()->put('impersonator_id', Auth::id());

        // Login as the tenant admin
        Auth::login($tenant->admin);

        // Redirect to tenant dashboard
        return redirect()->route('tenant.dashboard')
            ->with('success', 'You are now logged in as the school administrator.');
    }

    public function resetAdminPassword(Tenant $tenant)
    {
        if (!$tenant->admin) {
            return back()->with('error', 'No administrator found for this school.');
        }

        // Generate a new random password
        $password = Str::random(12);

        // Update the admin's password
        $tenant->admin->update([
            'password' => bcrypt($password)
        ]);

        // Send the new password to the admin's email
        Mail::to($tenant->admin->email)->send(new AdminPasswordReset($password));

        return back()->with('success', 'Administrator password has been reset and sent to their email.');
    }

    public function changePlan(Request $request, Tenant $tenant)
    {
        $request->validate([
            'plan' => ['required', 'string', 'in:basic,premium,enterprise']
        ]);

        // Get plan details from config
        $planDetails = config('subscription.plans.' . $request->plan);

        if (!$planDetails) {
            return back()->with('error', 'Invalid subscription plan selected.');
        }

        DB::beginTransaction();

        try {
            // Update or create subscription
            if (!$tenant->subscription) {
                $tenant->subscription()->create([
                    'status' => 'active',
                    'plan_id' => $planDetails['id'],
                    'starts_at' => now(),
                    'ends_at' => now()->addMonth(),
                    'price' => $planDetails['price'],
                    'features' => json_encode($planDetails['features']),
                ]);
            } else {
                $tenant->subscription->update([
                    'plan_id' => $planDetails['id'],
                    'price' => $planDetails['price'],
                    'features' => json_encode($planDetails['features']),
                ]);
            }

            // Update tenant's subscription plan
            $tenant->update([
                'subscription_plan' => $request->plan
            ]);

            DB::commit();

            return back()->with('success', 'Subscription plan has been updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update subscription plan. Please try again.');
        }
    }

    public function trash()
    {
        $tenants = Tenant::onlyTrashed()
            ->with(['subscription', 'admin'])
            ->latest('deleted_at')
            ->paginate();

        $stats = [
            'total' => Tenant::onlyTrashed()->count(),
            'expiringSoon' => Tenant::onlyTrashed()
                ->where('deleted_at', '<=', now()->addDays(7))
                ->count(),
            'storageUsed' => '-- MB', // Placeholder until we implement storage tracking
            'byStatus' => [
                'active' => 0,
                'trial' => 0,
                'canceled' => 0
            ]
        ];

        // Get subscription status counts
        $statusCounts = Tenant::onlyTrashed()
            ->whereHas('subscription', function ($query) {
                $query->whereNotNull('status');
            })
            ->join('subscriptions', 'tenants.id', '=', 'subscriptions.tenant_id')
            ->selectRaw('subscriptions.status, count(*) as count')
            ->groupBy('subscriptions.status')
            ->pluck('count', 'status')
            ->toArray();

        $stats['byStatus'] = array_merge($stats['byStatus'], $statusCounts);

        return Inertia::render('Admin/Tenants/Trash', [
            'tenants' => $tenants,
            'stats' => $stats
        ]);
    }

    public function restore(Tenant $tenant)
    {
        if (!$tenant->canBeRestored()) {
            return back()->with('error', 'This school cannot be restored anymore.');
        }

        try {
            DB::beginTransaction();
            
            $tenant->restore();
            
            DB::commit();
            
            return redirect()
                ->route('admin.tenants.show', $tenant)
                ->with('success', 'School has been restored successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            
            return back()->with('error', 'Failed to restore school.');
        }
    }

    public function forceDelete(Tenant $tenant)
    {
        try {
            DB::beginTransaction();
            
            // Permanently delete all related data
            $tenant->forceDelete();
            
            DB::commit();
            
            return redirect()
                ->route('admin.tenants.trash')
                ->with('success', 'School has been permanently deleted.');
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            
            return back()->with('error', 'Failed to permanently delete school.');
        }
    }

    public function exportTrash()
    {
        try {
            return Excel::download(
                new DeletedTenantsExport,
                'deleted-schools-' . now()->format('Y-m-d') . '.xlsx'
            );
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Failed to export deleted schools.');
        }
    }

    private function getSubscriptionPrice(string $planSlug): string
    {
        $plan = Plan::where('slug', $planSlug)
            ->where('is_active', true)
            ->firstOrFail();
        return (string) $plan->price;
    }

    private function getSubscriptionFeatures(string $planSlug): array
    {
        $plan = Plan::where('slug', $planSlug)
            ->where('is_active', true)
            ->firstOrFail();
        return $plan->features ?? [];
    }
} 