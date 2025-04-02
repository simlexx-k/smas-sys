<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SchoolClassController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $tenant = $user->tenant;
        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        $query = SchoolClass::where('tenant_id', $tenant->id)
            ->select('id', 'name')
            ->orderBy('name');

        if ($request->has('include') && $request->include === 'students') {
            $query->with(['students' => function($query) use ($tenant) {
                $query->where('tenant_id', $tenant->id);
            }]);
        }

        if ($request->query('paginate', true)) {
            return response()->json($query->paginate(10));
        }

        $classes = $query->get();
        return response()->json(['data' => $classes]);
    }

    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            $tenant = $user->tenant;
            if (!$tenant) {
                return response()->json(['error' => 'Tenant not found'], 404);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255'
            ]);

            // Automatically add tenant_id to the validated data
            $validated['tenant_id'] = $tenant->id;

            Log::info('Creating new class', [
                'tenant_id' => $tenant->id,
                'name' => $validated['name']
            ]);

            $class = SchoolClass::create($validated);

            Log::info('Class created successfully', [
                'class_id' => $class->id,
                'tenant_id' => $class->tenant_id
            ]);

            return response()->json($class, 201);
        } catch (\Exception $e) {
            Log::error('Error creating class', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to create class',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(SchoolClass $class)
    {
        // Check if class belongs to current tenant
        if ($class->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json($class);
    }

    public function update(Request $request, SchoolClass $class)
    {
        // Check if class belongs to current tenant
        if ($class->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $class->update($validated);
        return response()->json($class);
    }

    public function destroy(SchoolClass $class)
    {
        // Check if class belongs to current tenant
        if ($class->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $class->delete();
        return response()->json(null, 204);
    }
}
