<?php

namespace App\Http\Controllers;

use App\Support\CutoffPeriod;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeePortalController extends Controller
{
    public function index(Request $request): Response
    {
        $employee = $request->user()->employee;

        abort_unless($employee, 403, 'No employee record linked to this account.');

        $period = $request->filled('period_start')
            ? CutoffPeriod::forDate($request->input('period_start'))
            : CutoffPeriod::current();

        $records = $employee->attendanceForRange($period->start, $period->end);

        $totalHours = collect($records)->sum('total_hours');
        $daysPresent = collect($records)->whereNotNull('time_in')->count();

        return Inertia::render('my-attendance/Index', [
            'employee' => [
                'id_number' => $employee->id_number,
                'full_name' => $employee->full_name,
                'department' => $employee->department,
                'company' => $employee->company,
            ],
            'records' => $records,
            'totalHours' => round($totalHours, 2),
            'daysPresent' => $daysPresent,
            'period' => $period->toArray(),
            'previousPeriod' => $period->previous()->toArray(),
            'nextPeriod' => $period->next()->toArray(),
        ]);
    }
}
