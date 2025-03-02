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
use App\Traits\TenantBindable;

class TenantController extends Controller
{
    use TenantBindable;

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
            'role' => 'tenant-admin',
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
        $user = auth()->user();
        
        if (!$user || $user->role !== 'tenant-admin') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $tenant = $user->tenant;

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        return Inertia::render('Tenants/TenantDashboard', [
            'tenant' => $tenant,
        ]);
    }

    public function students()
    {
        $tenant = app('currentTenant');

        if (!$tenant) {
            return Inertia::render('Error', [
                'error' => 'Tenant not found',
            ]);
        }

        return Inertia::render('Tenants/Students', [
            'tenant' => $tenant,
        ]);
    }

    public function teachers()
    {
        $tenant = app('currentTenant');

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
        Log::info('[Classes] Request received');
        $tenant = app('currentTenant');

        if (!$tenant) {
            Log::error('[Classes] Tenant not found');
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        Log::info('[Classes] Fetching classes for tenant:', ['tenant_id' => $tenant->id]);
        $classes = $tenant->classes()->paginate(10);

        return Inertia::render('Tenants/Classes', [
            'classes' => $classes
        ]);
    }

    public function manageSubject($id = null)
    {
        $tenant = app('currentTenant');

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        $subject = null;
        if ($id) {
            $subject = $tenant->subjects()->find($id);
        }

        return Inertia::render('Tenants/ManageSubject', [
            'subject' => $subject,
            'classes' => $tenant->classes()->get()
        ]);
    }

    public function attendance()
    {
        $tenant = app('currentTenant');

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        return Inertia::render('Tenants/Attendance', [
            'tenant' => $tenant,
        ]);
    }

    public function adminRedirect()
    {
        $user = auth()->user();

        if ($user && $user->role === 'tenant-admin') {
            $tenant = $user->tenant;
            if ($tenant) {
                return redirect()->to('http://' . $tenant->domain . '.' . config('app.domain') . '/dashboard');
            }
        }

        return redirect('/');
    }

    public function getTenantsForAdmin(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'tenant-admin') {
            return response()->json(['tenants' => []]);
        }

        $tenants = Tenant::whereHas('admin', function ($query) use ($user) {
            $query->where('id', $user->id);
        })->get();

        \Log::info('Tenants data:', $tenants->toArray());

        return response()->json(['tenants' => $tenants]);
    }

    public function getTenantData(Request $request)
    {
        $user = $request->user();
        $tenant = Tenant::with(['admin', 'settings', 'configurations'])
            ->where('id', $user->tenant_id)
            ->first();

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        return response()->json([
            'id' => $tenant->hashed_id,
            'name' => $tenant->name,
            'domain' => $tenant->domain,
            'admin' => $tenant->admin,
            'settings' => $tenant->settings,
            'configurations' => $tenant->configurations,
            'created_at' => $tenant->created_at
        ]);
    }

    public function reportCards()
    {
        $tenant = app('currentTenant');

        if (!$tenant) {
            return Inertia::render('Error', [
                'error' => 'Tenant not found',
            ]);
        }

        return Inertia::render('Tenants/ReportCards', [
            'tenant' => $tenant,
        ]);
    }

    public function exams()
    {
        $tenant = app('currentTenant');

        if (!$tenant) {
            return Inertia::render('Error', [
                'error' => 'Tenant not found',
            ]);
        }

        return Inertia::render('Tenants/Exams', [
            'tenant' => $tenant,
        ]);
    }

    public function subjects()
    {
        $tenant = app('currentTenant');

        if (!$tenant) {
            return Inertia::render('Error', [
                'error' => 'Tenant not found',
            ]);
        }

        return Inertia::render('Tenants/Subjects', [
            'tenant' => $tenant,
        ]);
    }

    public function indexApi()
    {
        $tenants = Tenant::with('admin')->paginate(10)->append('hashed_id');
        return response()->json($tenants);
    }

    public function showApi($hashedId)
    {
        $id = Tenant::decodeHashedId($hashedId);
        $tenant = Tenant::find($id);

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        return response()->json(['tenant' => $tenant, 'hashed_id' => $tenant->hashed_id]);
    }

    public function storeApi(Request $request)
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
            'role' => 'tenant-admin',
        ]);

        return response()->json($tenant, 201);
    }

    public function updateApi(Request $request, $hashedId)
    {
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
        return response()->json($tenant);
    }

    public function destroyApi($hashedId)
    {
        $id = Tenant::decodeHashedId($hashedId);
        $tenant = Tenant::find($id);

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        $tenant->delete();
        return response()->json(null, 204);
    }
}
