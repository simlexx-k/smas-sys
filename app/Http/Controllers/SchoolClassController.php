<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::with('teacher', 'students')->paginate(10);
        return response()->json($classes);
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
