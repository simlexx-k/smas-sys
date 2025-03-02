<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index()
    {
        return Exam::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        return Exam::create($validated);
    }

    public function show(Exam $exam)
    {
        return $exam;
    }

    public function update(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'tenant_id' => 'sometimes|integer',
            'name' => 'sometimes|string|max:255',
            'date' => 'sometimes|date',
            'description' => 'nullable|string',
        ]);

        $exam->update($validated);
        return $exam;
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();
        return response()->noContent();
    }
}
