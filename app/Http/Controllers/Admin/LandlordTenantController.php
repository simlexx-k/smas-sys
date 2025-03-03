<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class LandlordTenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with('admin')->paginate(10)->append('hashed_id');
        return Inertia::render('Admin/Tenants/Index', [
            'tenants' => $tenants,
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
            return response()->json(['message' => 'Tenant created successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create tenant'], 500);
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
        return response()->json($tenant);
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();
        return response()->json(['message' => 'Tenant deleted successfully']);
    }
} 