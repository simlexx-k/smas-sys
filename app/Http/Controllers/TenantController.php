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
use App\Models\Lesson;
use App\Models\Activity;

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

    public function update(Request $request)
    {
        $tenant = auth()->user()->tenant;
        
        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'logo_url' => 'nullable|string|max:255',
        ]);

        $tenant->update($validated);
        return response()->json($tenant);
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
        $tenant = auth()->user()->tenant;
        
        if (!$tenant) {
            abort(404, 'Tenant not found');
        }

        return Inertia::render('Dashboard', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'email' => $tenant->email,
                'phone' => $tenant->phone,
                'address' => $tenant->address,
                'logo_url' => $tenant->logo_url,
                'created_at' => $tenant->created_at
            ],
            'user' => [
                'role' => auth()->user()->role
            ]
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

        $teachers = $tenant->teachers()
            ->with(['user', 'subjects'])
            ->get()
            ->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->user->name,
                    'email' => $teacher->user->email,
                    'status' => $teacher->status,
                    'employee_id' => $teacher->employee_id,
                    'department' => $teacher->department,
                    'subjects' => $teacher->subjects->pluck('name')->toArray(),
                    'joining_date' => $teacher->joining_date
                ];
            });

        // Get all subjects for the tenant
        $subjects = $tenant->subjects()->get(['id', 'name'])->toArray();

        // Get departments (optional)
        $departments = $tenant->teachers()
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->values()
            ->toArray();

        return Inertia::render('Tenants/Teachers', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name
            ],
            'teachers' => $teachers,
            'departments' => $departments,
            'subjects' => $subjects
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
        $tenant = auth()->user()->tenant;
        
        return Inertia::render('Tenants/ReportCards', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'email' => $tenant->email,
                'phone' => $tenant->phone,
                'address' => $tenant->address,
                'logo_url' => $tenant->logo_url,
            ]
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

    public function bulkReportCards()
    {
        $tenant = auth()->user()->tenant;
        
        return Inertia::render('Tenants/BulkReportCards', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'email' => $tenant->email,
                'phone' => $tenant->phone,
                'address' => $tenant->address,
                'logo_url' => $tenant->logo_url,
            ]
        ]);
    }

    public function settings()
    {
        $tenant = auth()->user()->tenant;
        
        if (!$tenant) {
            abort(404, 'Tenant not found');
        }

        return Inertia::render('School', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'email' => $tenant->email,
                'phone' => $tenant->phone,
                'address' => $tenant->address,
                'logo_url' => $tenant->logo_url,
                'created_at' => $tenant->created_at
            ]
        ]);
    }

    public function updateSettings(Request $request)
    {
        $tenant = auth()->user()->tenant;
        
        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'logo_url' => 'nullable|string|max:255',
        ]);

        $tenant->update($validated);
        return response()->json($tenant);
    }

    public function teacherDashboard()
    {
        $user = auth()->user();
        $tenant = $user->tenant;
        
        if (!$tenant) {
            abort(404, 'Tenant not found');
        }

        // Get teacher's classes with student counts
        $classes = $tenant->getTeacherClasses($user);

        // Get upcoming lessons with subject and class details
        $upcomingLessons = Lesson::where('tenant_id', $tenant->id)
            ->where('teacher_id', $user->teacher->id)
            ->where('start_time', '>=', now())
            ->with(['subject:id,name', 'class:id,name']) // Only select needed fields
            ->orderBy('start_time', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($lesson) {
                return [
                    'id' => $lesson->id,
                    'subject' => [
                        'name' => $lesson->subject?->name ?? 'No Subject'
                    ],
                    'class' => [
                        'name' => $lesson->class?->name ?? 'No Class'
                    ],
                    'start_time' => $lesson->start_time,
                    'end_time' => $lesson->end_time,
                    'status' => $lesson->status
                ];
            });

        // Get recent activities
        $recentActivities = Activity::where('user_id', $user->id)
            ->orWhere(function($query) use ($user) {
                $query->where('subject_type', 'App\Models\Teacher')
                      ->where('subject_id', $user->teacher->id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'type' => $activity->type,
                    'description' => $activity->description,
                    'created_at' => $activity->created_at
                ];
            });

        return Inertia::render('Teacher/Dashboard', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
            ],
            'teacher' => $user->teacher,
            'classes' => $classes,
            'upcoming_lessons' => $upcomingLessons,
            'recent_activities' => $recentActivities
        ]);
    }
}
