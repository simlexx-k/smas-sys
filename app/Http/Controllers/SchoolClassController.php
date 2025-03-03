<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function index(Request $request)
    {
        \Log::info('Auth check:', [
            'is_authenticated' => auth()->check(),
            'user' => auth()->user(),
            'tenant_id' => $request->query('tenant_id')
        ]);

        // Verify user has access to the tenant
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $requestedTenantId = $request->query('tenant_id');
        
        if ($user->tenant_id != $requestedTenantId && !$user->hasRole('landlord')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = SchoolClass::where('tenant_id', $requestedTenantId)
            ->select('id', 'name')
            ->orderBy('name');

        if ($request->has('include') && $request->include === 'students') {
            $query->with(['students' => function($query) use ($requestedTenantId) {
                $query->where('tenant_id', $requestedTenantId);
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
        $validated = $request->validate([
            'tenant_id' => 'required|integer',
            'name' => 'required|string|max:255'
        ]);

        $class = SchoolClass::create($validated);
        return response()->json($class, 201);
    }

    public function show(SchoolClass $class)
    {
        return response()->json($class);
    }

    public function update(Request $request, SchoolClass $class)
    {
        $class->update($request->all());
        return response()->json($class);
    }

    public function destroy(SchoolClass $class)
    {
        $class->delete();
        return response()->json(null, 204);
    }
}
