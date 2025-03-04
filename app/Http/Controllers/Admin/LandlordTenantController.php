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

        return Inertia::render('Admin/Tenants/Index', [
            'schools' => [
                'data' => $tenants->items(),
                'links' => $tenants->links()
            ],
            'filters' => $request->only(['search', 'status'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Tenants/Create');
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
        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255', Rule::unique('tenants')],
            'name' => ['required', 'string', 'max:255'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_password' => ['required', 'string', 'min:8'],
            'school_type' => ['required', 'string'],
            'subscription_plan' => ['required', 'string'],
            'status' => ['required', 'string'],
        ]);

        DB::beginTransaction();
        try {
            $tenant = Tenant::create([
                'domain' => $validated['domain'],
                'name' => $validated['name'],
                'school_type' => $validated['school_type'],
                'subscription_plan' => $validated['subscription_plan'],
                'status' => $validated['status']
            ]);

            User::create([
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'password' => Hash::make($validated['admin_password']),
                'tenant_id' => $tenant->id,
                'role' => 'tenant-admin',
            ]);

            DB::commit();
            return redirect()
                ->route('admin.tenants.show', $tenant)
                ->with('success', 'School created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create school']);
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
        // Validate that the tenant can be deleted
        if ($tenant->subscription && $tenant->subscription->status === 'active') {
            return back()->with('error', 'Cannot delete a school with an active subscription.');
        }

        DB::beginTransaction();

        try {
            // Delete related records
            $tenant->domains()->delete();
            $tenant->subscription()->delete();
            
            // Delete the admin user
            if ($tenant->admin) {
                $tenant->admin->delete();
            }

            // Delete the tenant
            $tenant->delete();

            DB::commit();

            return redirect()
                ->route('admin.tenants.index')
                ->with('success', 'School has been successfully deleted.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete school. Please try again.');
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
        $tenant->load(['subscription', 'admin']);

        return Inertia::render('Admin/Tenants/Edit', [
            'tenant' => $tenant,
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
            ]
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
} 