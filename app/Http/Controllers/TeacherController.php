<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    //public function __construct()
    //{
    //    $this->middleware(['role:tenant-admin', 'tenant']);
    //}

    public function store(Request $request)
    {
        // Ensure the user is a tenant admin
        //if (!auth()->user()->role === User::ROLE_TENANT_ADMIN) {
        //    abort(403, 'Unauthorized action.');
       // }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'employee_id' => 'required|string|max:255|unique:teachers,employee_id',
            'phone' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'joining_date' => 'required|date',
            'qualification' => 'required|string|max:255',
            'subjects' => 'required|array|min:1',
            'subjects.*' => 'exists:subjects,id'
        ]);

        try {
            DB::beginTransaction();

            // Create user with current tenant's ID
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'tenant_id' => auth()->user()->tenant_id,
                'role' => User::ROLE_TEACHER
            ]);

            // Create teacher
            $teacher = Teacher::create([
                'user_id' => $user->id,
                'tenant_id' => auth()->user()->tenant_id,
                'employee_id' => $validated['employee_id'],
                'department' => $validated['department'],
                'qualification' => $validated['qualification'],
                'joining_date' => $validated['joining_date'],
                'phone' => $validated['phone'],
                'status' => 'Active'
            ]);

            // Attach subjects
            $teacher->subjects()->attach($validated['subjects']);

            DB::commit();

            return redirect()->back()->with('success', 'Teacher created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Failed to create teacher']);
        }
    }
} 