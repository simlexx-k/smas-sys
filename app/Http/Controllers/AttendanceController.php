<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::with(['tenant', 'student'])->paginate(10);
        return response()->json($attendances);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'tenant_id' => 'required|exists:tenants,id',
                'student_id' => 'required|exists:students,id',
                'class_id' => 'required|exists:classes,id',
                'date' => 'required|date',
                'status' => 'required|in:present,absent,late'
            ]);

            $attendance = Attendance::create($validated);
            return response()->json($attendance, 201);
        } catch (\Exception $e) {
            Log::error('Attendance store error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Server Error'], 500);
        }
    }

    public function show(Attendance $attendance)
    {
        return response()->json($attendance);
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:present,absent,late',
            'remarks' => 'nullable|string'
        ]);

        $attendance->update($validated);
        return response()->json($attendance);
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return response()->json(null, 204);
    }

    public function getByTenant($tenantId)
    {
        $attendances = Attendance::where('tenant_id', $tenantId)->get();
        return response()->json($attendances);
    }
}
