<?php

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Models\Employee;
use App\Support\CutoffPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function index(Request $request): Response
    {
        $period = $request->filled('period_start')
            ? CutoffPeriod::forDate($request->input('period_start'))
            : CutoffPeriod::current();

        $employees = Employee::query()->active()->orderBy('last_name')->get();

        $attendance = $employees->map(function (Employee $employee) use ($period) {
            $records = $employee->attendanceForRange($period->start, $period->end);

            $totalHours = collect($records)->sum('total_hours');

            return [
                'employee' => [
                    'id' => $employee->id,
                    'id_number' => $employee->id_number,
                    'full_name' => $employee->full_name,
                    'department' => $employee->department,
                ],
                'records' => $records,
                'total_hours' => round($totalHours, 2),
                'days_present' => collect($records)->whereNotNull('time_in')->count(),
            ];
        });

        return Inertia::render('attendance/Index', [
            'attendance' => $attendance,
            'period' => $period->toArray(),
            'previousPeriod' => $period->previous()->toArray(),
            'nextPeriod' => $period->next()->toArray(),
        ]);
    }

    public function updateManualTimeOut(Request $request, Checkin $checkin): RedirectResponse
    {
        $validated = $request->validate([
            'manual_time_out' => ['required', 'date'],
        ]);

        $checkin->update($validated);

        return back()->with('success', 'Manual time-out saved.');
    }
}
