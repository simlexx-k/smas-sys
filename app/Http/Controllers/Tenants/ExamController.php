<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index()
    {
        $exams = Exam::all();
        return view('tenants.exams.index', compact('exams'));
    }

    public function create()
    {
        return view('tenants.exams.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        Exam::create($validatedData);

        return redirect()->route('tenants.exams.index')->with('success', 'Exam created successfully.');
    }

    public function show(Exam $exam)
    {
        return view('tenants.exams.show', compact('exam'));
    }

    public function edit(Exam $exam)
    {
        return view('tenants.exams.edit', compact('exam'));
    }

    public function update(Request $request, Exam $exam)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $exam->update($validatedData);

        return redirect()->route('tenants.exams.index')->with('success', 'Exam updated successfully.');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();
        return redirect()->route('tenants.exams.index')->with('success', 'Exam deleted successfully.');
    }

    public function indexApi()
    {
        $exams = Exam::all();
        return response()->json($exams, 200);
    }

    public function showApi(Exam $exam)
    {
        return response()->json($exam);
    }

    public function storeApi(Request $request)
    {
        $validatedData = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'name' => 'required|string|max:255',
            'date' => 'required|date',
        ]);

        \Log::info('Validated Data:', $validatedData);

        $exam = Exam::create($validatedData);
        \Log::info('Exam created successfully:', $exam);
        return response()->json($exam, 201);
    }

    public function updateApi(Request $request, Exam $exam)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $exam->update($validatedData);
        return response()->json($exam);
    }

    public function destroyApi(Exam $exam)
    {
        $exam->delete();
        return response()->json(null, 204);
    }
}
