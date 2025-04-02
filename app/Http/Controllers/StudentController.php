<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

class StudentController extends Controller
{
    public function index()
    {
        // Get the current tenant from the authenticated user
        $tenant = auth()->user()->tenant;
        
        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        // Get available classes for the tenant
        $classes = SchoolClass::where('tenant_id', $tenant->id)
            ->select('id', 'name')
            ->get();

        // Filter students by tenant_id and include class information
        $students = Student::where('students.tenant_id', $tenant->id)
            ->join('classes', 'students.school_class_id', '=', 'classes.id')
            ->select('students.*', 'classes.name as class_name')
            ->get();

        return response()->json([
            'students' => $students,
            'classes' => $classes
        ]);
    }

    public function store(Request $request)
    {
        Log::info('Starting student creation', ['request' => Arr::except($request->all(), ['password', 'admin_password'])]);

        try {
            // Get the current tenant
            $tenant = auth()->user()->tenant;
            
            if (!$tenant) {
                throw new \Exception('Tenant not found');
            }

            // Validate the class belongs to the tenant
            if (!SchoolClass::where('id', $request->school_class_id)
                ->where('tenant_id', $tenant->id)
                ->exists()) {
                throw new \Exception('Invalid class selected');
            }

            $validated = $request->validate([
                'school_class_id' => 'required|integer|exists:classes,id',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'date_of_birth' => 'required|date',
                'gender' => 'required|string|in:male,female,other',
                'address' => 'required|string',
                'phone_number' => 'required|string',
                'email' => 'required|email|unique:students,email,NULL,id,tenant_id,'.$tenant->id
            ]);

            // Add tenant_id to validated data
            $validated['tenant_id'] = $tenant->id;

            Log::info('Validation passed', ['data' => $validated]);

            // Create student with explicit attributes
            $student = Student::create([
                'tenant_id' => $validated['tenant_id'],
                'school_class_id' => $validated['school_class_id'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'address' => $validated['address'],
                'phone_number' => $validated['phone_number'],
                'email' => $validated['email'],
            ]);
            
            Log::info('Student created successfully', [
                'student_id' => $student->id,
                'class_id' => $student->school_class_id
            ]);

            // Load the class name for the response
            $student->load('schoolClass:id,name');

            return response()->json($student, 201);
        } catch (\Exception $e) {
            Log::error('Error creating student', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'school_class_id')) {
                $errorMessage = 'Please select a valid class for the student.';
            }

            return response()->json([
                'message' => 'Failed to create student',
                'error' => $errorMessage
            ], 500);
        }
    }

    public function show(Student $student)
    {
        // Check if student belongs to current tenant
        if ($student->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json($student);
    }

    public function update(Request $request, Student $student)
    {
        // Check if student belongs to current tenant
        if ($student->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $student->update($request->all());
        return response()->json($student);
    }

    public function destroy(Student $student)
    {
        // Check if student belongs to current tenant
        if ($student->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $student->delete();
        return response()->json(null, 204);
    }
}
