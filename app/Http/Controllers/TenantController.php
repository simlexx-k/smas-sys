<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with('admin')->paginate(10)->append('hashed_id');
        \Log::info('Tenants data:', $tenants->toArray());
        return Inertia::render('Tenants/Index', [
            'tenants' => $tenants,
        ]);
    }

    public function create()
    {
        return Inertia::render('Tenants/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255', Rule::unique('tenants')],
            'name' => ['required', 'string', 'max:255'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_password' => ['required', 'string', 'min:8'],
        ]);

        $tenant = Tenant::create([
            'domain' => $validated['domain'],
            'name' => $validated['name'],
        ]);

        $admin = User::create([
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => Hash::make($validated['admin_password']),
            'tenant_id' => $tenant->id,
            'role' => 'admin',
        ]);

        return redirect()->route('tenants.index');
    }

    public function edit($hashedId)
    {
        \Log::info("Hashed ID received: " . $hashedId);
        $id = Tenant::decodeHashedId($hashedId);
        Log::info("Decoded ID: {$id}");

        $tenant = Tenant::find($id);
        Log::info("Tenant found: " . ($tenant ? $tenant->id : 'null'));

        if (!$tenant) {
            return redirect()->route('tenants.index')->withErrors(['error' => 'Tenant not found']);
        }

        return Inertia::render('Tenants/Edit', [
            'tenant' => $tenant->load('admin'),
            'users' => User::where('tenant_id', $tenant->id)->get(),
        ]);
    }

    public function update(Request $request, $hashedId)
    {
        \Log::info("Hashed ID received: " . $hashedId);
        $tenant = Tenant::findByHashedId($hashedId);

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        $validated = $request->validate([
            'domain' => ['required', 'string', 'max:255', Rule::unique('tenants')->ignore($tenant->id)],
            'name' => ['required', 'string', 'max:255'],
            'admin_id' => ['required', 'exists:users,id'],
        ]);

        $tenant->update($validated);

        return redirect()->route('tenants.index');
    }

    public function show($hashedId)
    {
        \Log::info("Hashed ID received: " . $hashedId);
        $id = Tenant::decodeHashedId($hashedId);
        $tenant = Tenant::find($id);

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        return response()->json(['tenant' => $tenant, 'hashed_id' => $tenant->hashed_id]);
    }

    public function destroy($hashedId)
    {
        \Log::info("Hashed ID received: " . $hashedId);
        $id = Tenant::decodeHashedId($hashedId);
        $tenant = Tenant::find($id);

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        $tenant->delete();
        return response()->json(['message' => 'Tenant deleted successfully']);
    }

    public function dashboard()
    {
        $tenant = Tenant::current();

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        return Inertia::render('Tenants/TenantDashboard', [
            'tenant' => $tenant,
        ]);
    }

    public function students()
    {
        $tenant = Tenant::current();

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        return Inertia::render('Tenants/Students', [
            'tenant' => $tenant,
        ]);
    }

    public function teachers()
    {
        $tenant = Tenant::current();

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        return Inertia::render('Tenants/Teachers', [
            'tenant' => $tenant,
        ]);
    }

    public function academics()
    {
        $tenant = Tenant::current();

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        return Inertia::render('Tenants/Academics', [
            'tenant' => $tenant,
        ]);
    }

    public function classes()
    {
        $tenant = Tenant::current();

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        return Inertia::render('Tenants/Classes', [
            'tenant' => $tenant,
        ]);
    }
}
