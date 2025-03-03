<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SubjectController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        return Subject::where('tenant_id', auth()->user()->tenant_id)->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects',
            'description' => 'nullable|string',
            'class_id' => 'required|exists:classes,id',
            'tenant_id' => 'required|exists:tenants,id'
        ]);

        $subject = Subject::create($validated);
        return response()->json($subject, 201);
    }

    public function show(Subject $subject)
    {
        if ($subject->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized action.');
        }
        return $subject;
    }

    public function update(Request $request, Subject $subject)
    {
        if ($subject->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', 
                Rule::unique('subjects')->ignore($subject->id)],
            'description' => 'nullable|string',
            'class_id' => 'required|exists:classes,id'
        ]);

        $subject->update($validated);
        return response()->json($subject);
    }

    public function destroy(Subject $subject)
    {
        if ($subject->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized action.');
        }
        
        $subject->delete();
        return response()->noContent();
    }
}
