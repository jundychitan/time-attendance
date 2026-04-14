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

        $query = Employee::query()->active();

        // Company scope
        if (auth()->user()->company) {
            $query->where('company', auth()->user()->company);
        }

        $employees = $query->orderBy('last_name')->get();

        $attendance = $employees->map(function (Employee $employee) use ($period) {
            $records = $employee->attendanceForRange($period->start, $period->end);

            $recordsCollection = collect($records);
            $totalRegular = $recordsCollection->sum('regular_hours');
            $totalOT = $recordsCollection->sum('overtime_hours');

            return [
                'employee' => [
                    'id' => $employee->id,
                    'id_number' => $employee->id_number,
                    'full_name' => $employee->full_name,
                    'department' => $employee->department,
                ],
                'records' => $records,
                'total_regular_hours' => round($totalRegular, 2),
                'total_overtime_hours' => round($totalOT, 2),
                'days_present' => $recordsCollection->whereNotNull('time_in')->count(),
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

        $checkin->update([
            ...$validated,
            'manual_time_out_status' => 'pending',
            'approved_by' => null,
        ]);

        return back()->with('success', 'Manual time-out submitted for approval.');
    }

    public function approveManualTimeOut(Checkin $checkin): RedirectResponse
    {
        $checkin->update([
            'manual_time_out_status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Manual time-out approved.');
    }

    public function rejectManualTimeOut(Checkin $checkin): RedirectResponse
    {
        $checkin->update([
            'manual_time_out_status' => 'rejected',
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Manual time-out rejected.');
    }

    public function approveOvertime(Checkin $checkin): RedirectResponse
    {
        $checkin->update([
            'overtime_status' => 'approved',
            'overtime_approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Overtime approved.');
    }

    public function rejectOvertime(Checkin $checkin): RedirectResponse
    {
        $checkin->update([
            'overtime_status' => 'rejected',
            'overtime_approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Overtime rejected.');
    }
}
