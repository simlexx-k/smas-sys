<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class TenantController extends Controller
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function index()
    {
        $tenants = Tenant::with(['subscription', 'domains'])
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when(request('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Schools/Index', [
            'schools' => $tenants,
            'filters' => request()->only(['search', 'status'])
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Schools/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
            'logo' => 'nullable|image|max:2048'
        ]);

        $tenant = $this->tenantService->create($validated);

        return redirect()
            ->route('admin.schools.show', $tenant)
            ->with('success', 'School created successfully.');
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['subscription.plan', 'domains', 'admin']);
        
        $usageStats = $this->tenantService->getUsageStats($tenant);
        $classStats = $this->tenantService->getClassStats($tenant);
        $studentStats = $this->tenantService->getStudentStats($tenant);

        return Inertia::render('Admin/Schools/Show', [
            'school' => $tenant,
            'usageStats' => $usageStats,
            'classStats' => $classStats,
            'studentStats' => $studentStats
        ]);
    }

    public function edit(Tenant $tenant)
    {
        return Inertia::render('Admin/Schools/Edit', [
            'school' => $tenant
        ]);
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
            'logo' => 'nullable|image|max:2048'
        ]);

        $tenant = $this->tenantService->update($tenant, $validated);

        return redirect()
            ->route('admin.schools.show', $tenant)
            ->with('success', 'School updated successfully.');
    }

    public function destroy(Tenant $tenant)
    {
        $this->tenantService->delete($tenant);

        return redirect()
            ->route('admin.schools.index')
            ->with('success', 'School deleted successfully.');
    }

    // Stats methods
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
} 