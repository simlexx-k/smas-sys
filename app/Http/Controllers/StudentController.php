<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::all();
        return response()->json($students);
    }

    public function store(Request $request)
    {
        Log::info('Starting student creation', ['request' => $request->all()]);

        try {
            $validated = $request->validate([
                'tenant_id' => 'required|integer',
                'school_class_id' => 'required|integer',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'date_of_birth' => 'required|date',
                'gender' => 'required|string|in:male,female,other',
                'address' => 'required|string',
                'phone_number' => 'required|string',
                'email' => 'required|email|unique:students'
            ]);

            Log::info('Validation passed', ['data' => $validated]);

            $student = Student::create($validated);
            Log::info('Student created successfully', ['student' => $student]);

            return response()->json($student, 201);
        } catch (\Exception $e) {
            Log::error('Error creating student', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to create student',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Student $student)
    {
        return response()->json($student);
    }

    public function update(Request $request, Student $student)
    {
        $student->update($request->all());
        return response()->json($student);
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return response()->json(null, 204);
    }
}
