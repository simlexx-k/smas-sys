<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Activity;
use App\Notifications\WelcomeTenantAdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class SchoolRegistrationController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:tenants,email',
                'phone' => 'required|string|max:20',
                'address' => 'required|string|max:500',
                'domain' => 'required|string|max:255|unique:tenants,domain|alpha_dash',
                'school_type' => 'required|in:primary,secondary',
                'logo' => 'nullable|image|max:2048',
                'admin_name' => 'required|string|max:255',
                'admin_email' => 'required|string|email|max:255|unique:users,email',
                'admin_password' => ['required', 'confirmed', Password::defaults()],
            ]);

            Log::info('School registration validation passed', [
                'school_name' => $validated['name'],
                'domain' => $validated['domain']
            ]);

            DB::beginTransaction();

            try {
                // First create admin user
                $admin = User::create([
                    'name' => $validated['admin_name'],
                    'email' => $validated['admin_email'],
                    'password' => Hash::make($validated['admin_password']),
                    'role' => 'tenant-admin',
                ]);

                Log::info('Admin user created successfully', ['user_id' => $admin->id]);

                // Set user context for tenant creation
                Tenant::setUserContext($admin->id);

                // Create the tenant
                $tenant = Tenant::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'address' => $validated['address'],
                    'domain' => $validated['domain'],
                    'school_type' => $validated['school_type'],
                    'status' => Tenant::STATUS_ACTIVE,
                    'subscription_plan' => 'trial',
                    'subscription_ends_at' => now()->addDays(30),
                ]);

                Log::info('Tenant created successfully', ['tenant_id' => $tenant->id]);

                // Update admin with tenant_id
                $admin->tenant_id = $tenant->id;
                $admin->save();

                // Handle logo upload if provided
                if ($request->hasFile('logo')) {
                    try {
                        $path = $request->file('logo')->store('tenant-logos', 'public');
                        $tenant->logo_url = Storage::url($path);
                        $tenant->save();
                        Log::info('Logo uploaded successfully', ['path' => $path]);
                    } catch (\Exception $e) {
                        Log::error('Logo upload failed', [
                            'error' => $e->getMessage(),
                            'tenant_id' => $tenant->id
                        ]);
                    }
                }

                // Manually create activity log
                Activity::create([
                    'user_id' => $admin->id,
                    'tenant_id' => $tenant->id,
                    'type' => 'create',
                    'action' => 'Tenant Created',
                    'description' => "New tenant '{$tenant->name}' was created",
                    'subject_type' => Tenant::class,
                    'subject_id' => $tenant->id,
                    'metadata' => [
                        'model_name' => $tenant->name,
                        'changes' => [
                            'created' => $tenant->toArray()
                        ]
                    ]
                ]);

                // Send welcome email
                try {
                    $admin->notify(new WelcomeTenantAdminNotification(
                        $tenant,
                        $validated['admin_password']
                    ));
                    Log::info('Welcome email sent successfully', [
                        'user_id' => $admin->id,
                        'tenant_id' => $tenant->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send welcome email', [
                        'error' => $e->getMessage(),
                        'user_id' => $admin->id,
                        'tenant_id' => $tenant->id
                    ]);
                }

                DB::commit();

                // Log the admin user in
                auth()->login($admin);

                Log::info('School registration completed successfully', [
                    'tenant_id' => $tenant->id,
                    'admin_id' => $admin->id
                ]);

                return redirect()->route('tenant.dashboard')
                    ->with('success', 'School registered successfully! Please check your email for login details.');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('School registration failed during database transaction', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'data' => $validated
                ]);
                
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('School registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['admin_password', 'admin_password_confirmation'])
            ]);
            
            return back()
                ->withInput($request->except(['admin_password', 'admin_password_confirmation']))
                ->withErrors(['error' => 'Failed to register school: ' . $e->getMessage()]);
        }
    }
} 