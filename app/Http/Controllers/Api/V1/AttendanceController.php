<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Checkin;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    /**
     * Daily attendance summary for all employees.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['sometimes', 'date'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
            'employee_id' => ['sometimes', 'exists:employees,id'],
        ]);

        $date = $request->input('date', now()->toDateString());
        $startDate = $request->input('start_date', $date);
        $endDate = $request->input('end_date', $date);

        $query = Employee::query()->active();

        if ($request->filled('employee_id')) {
            $query->where('id', $request->input('employee_id'));
        }

        $employees = $query->get();
        $attendance = [];

        foreach ($employees as $employee) {
            $records = $employee->attendanceForRange($startDate, $endDate);

            foreach ($records as $record) {
                $attendance[] = [
                    'employee' => [
                        'id' => $employee->id,
                        'id_number' => $employee->id_number,
                        'full_name' => $employee->full_name,
                    ],
                    ...$record,
                ];
            }
        }

        return response()->json(['data' => $attendance]);
    }

    /**
     * Aggregated attendance summary for the dashboard.
     */
    public function summary(Request $request): JsonResponse
    {
        $date = Carbon::parse($request->input('date', now()->toDateString()));

        $totalEmployees = Employee::query()->active()->count();

        $employeesWithCheckins = Checkin::query()
            ->whereDate('captured_at', $date)
            ->distinct('employee_id')
            ->count('employee_id');

        $presentToday = $employeesWithCheckins;
        $absentToday = $totalEmployees - $presentToday;

        // Average hours worked today
        $employees = Employee::query()->active()
            ->whereHas('checkins', fn ($q) => $q->whereDate('captured_at', $date))
            ->get();

        $totalHours = 0;
        $employeesWithHours = 0;

        foreach ($employees as $employee) {
            $record = $employee->attendanceForDate($date);
            if ($record['total_hours'] !== null) {
                $totalHours += $record['total_hours'];
                $employeesWithHours++;
            }
        }

        $avgHours = $employeesWithHours > 0 ? round($totalHours / $employeesWithHours, 2) : 0;

        // Recent checkins
        $recentCheckins = Checkin::query()
            ->with('employee')
            ->orderByDesc('captured_at')
            ->limit(10)
            ->get()
            ->map(fn (Checkin $c) => [
                'id' => $c->id,
                'employee_name' => $c->employee->full_name,
                'employee_id_number' => $c->employee->id_number,
                'captured_at' => $c->captured_at->toDateTimeString(),
                'location_name' => $c->location_name,
            ]);

        return response()->json([
            'data' => [
                'date' => $date->toDateString(),
                'total_employees' => $totalEmployees,
                'present_today' => $presentToday,
                'absent_today' => $absentToday,
                'average_hours' => $avgHours,
                'recent_checkins' => $recentCheckins,
            ],
        ]);
    }
}
