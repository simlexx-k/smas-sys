<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        return Subject::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|integer',
            'name' => 'required|string',
            'code' => 'required|string|unique:subjects',
            'description' => 'nullable|string',
            'class_id' => 'required|exists:classes,id',
        ]);

        return Subject::create($validated);
    }

    public function show(Subject $subject)
    {
        return $subject;
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'tenant_id' => 'sometimes|integer',
            'name' => 'sometimes|string',
            'code' => 'sometimes|string|unique:subjects,code,' . $subject->id,
            'description' => 'nullable|string',
            'class_id' => 'sometimes|exists:classes,id',
        ]);

        $subject->update($validated);
        return $subject;
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return response()->noContent();
    }
}
