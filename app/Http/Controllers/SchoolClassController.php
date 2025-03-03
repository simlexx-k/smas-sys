<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $request->query('tenant_id');
        if (!$tenantId) {
            return response()->json(['error' => 'Tenant ID is required'], 400);
        }

        $query = SchoolClass::where('tenant_id', $tenantId)
            ->select('id', 'name')  // Only select needed fields
            ->orderBy('name');

        // If pagination is requested
        if ($request->query('paginate', true)) {
            return response()->json($query->paginate(10));
        }

        // Return all results without pagination
        return response()->json($query->get());
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
