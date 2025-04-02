<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $tenant = auth()->user()->tenant;
        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        return Exam::where('tenant_id', $tenant->id)->get();
    }

    public function store(Request $request)
    {
        try {
            $tenant = auth()->user()->tenant;
            if (!$tenant) {
                return response()->json(['error' => 'Tenant not found'], 404);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'term_id' => 'required|exists:terms,id',
                'status' => 'required|in:' . implode(',', Exam::getValidStatuses())
            ]);

            // Add tenant_id to validated data
            $validated['tenant_id'] = $tenant->id;

            // Ensure status is a string
            $validated['status'] = (string) $validated['status'];

            Log::info('Creating exam with data:', $validated);

            $exam = Exam::create($validated);

            return response()->json($exam, 201);
        } catch (\Exception $e) {
            Log::error('Error creating exam:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to create exam',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Exam $exam)
    {
        if ($exam->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json($exam);
    }

    public function update(Request $request, Exam $exam)
    {
        if ($exam->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'term_id' => 'sometimes|required|exists:terms,id',
            'status' => 'sometimes|required|in:draft,published,completed'
        ]);

        $exam->update($validated);
        return response()->json($exam);
    }

    public function destroy(Exam $exam)
    {
        if ($exam->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $exam->delete();
        return response()->json(null, 204);
    }
}
