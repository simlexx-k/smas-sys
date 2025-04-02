<?php

namespace App\Http\Controllers;

use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TermController extends Controller
{
    public function index()
    {
        $tenant = auth()->user()->tenant;
        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        return Term::where('tenant_id', $tenant->id)
                  ->orderBy('start_date', 'desc')
                  ->get()
                  ->map(function($term) {
                      return [
                          'id' => $term->id,
                          'name' => $term->name,
                          'formatted_name' => $term->formatted_name,
                          'start_date' => $term->start_date,
                          'end_date' => $term->end_date,
                          'academic_year' => $term->academic_year,
                          'status' => $term->status
                      ];
                  });
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
                'end_date' => 'required|date|after:start_date',
                'academic_year' => 'required|string|max:9',
                'status' => 'required|in:active,inactive,completed'
            ]);

            $validated['tenant_id'] = $tenant->id;

            $term = Term::create($validated);

            return response()->json($term, 201);
        } catch (\Exception $e) {
            Log::error('Error creating term:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to create term',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Term $term)
    {
        if ($term->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'id' => $term->id,
            'name' => $term->name,
            'formatted_name' => $term->formatted_name,
            'start_date' => $term->start_date,
            'end_date' => $term->end_date,
            'academic_year' => $term->academic_year,
            'status' => $term->status
        ]);
    }

    public function update(Request $request, Term $term)
    {
        if ($term->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            'academic_year' => 'sometimes|required|string|max:9',
            'status' => 'sometimes|required|in:active,inactive,completed'
        ]);

        $term->update($validated);
        return response()->json($term);
    }

    public function destroy(Term $term)
    {
        if ($term->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $term->delete();
        return response()->json(null, 204);
    }
} 