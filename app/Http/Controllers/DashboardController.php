<?php

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Models\Employee;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $today = Carbon::today();

        $employeeQuery = Employee::query()->active();

        // Company scope
        if (auth()->user()->company) {
            $employeeQuery->where('company', auth()->user()->company);
        }

        $totalEmployees = (clone $employeeQuery)->count();
        $employeeIds = (clone $employeeQuery)->pluck('id');

        $presentToday = Checkin::query()
            ->whereDate('captured_at', $today)
            ->whereIn('employee_id', $employeeIds)
            ->distinct('employee_id')
            ->count('employee_id');

        $absentToday = $totalEmployees - $presentToday;

        // Average hours worked today
        $employees = (clone $employeeQuery)
            ->whereHas('checkins', fn ($q) => $q->whereDate('captured_at', $today))
            ->get();

        $totalHours = 0;
        $employeesWithHours = 0;

        foreach ($employees as $employee) {
            $record = $employee->attendanceForDate($today);
            if ($record['total_hours'] !== null) {
                $totalHours += $record['total_hours'];
                $employeesWithHours++;
            }
        }

        $avgHours = $employeesWithHours > 0 ? round($totalHours / $employeesWithHours, 2) : 0;

        // Recent checkins (company-scoped)
        $recentCheckins = Checkin::query()
            ->with('employee')
            ->whereIn('employee_id', $employeeIds)
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

        // Monthly attendance trend (current month)
        $startOfMonth = $today->copy()->startOfMonth();
        $daysInMonth = [];
        $current = $startOfMonth->copy();

        while ($current->lte($today)) {
            $presentCount = Checkin::query()
                ->whereDate('captured_at', $current)
                ->whereIn('employee_id', $employeeIds)
                ->distinct('employee_id')
                ->count('employee_id');

            $daysInMonth[] = [
                'date' => $current->toDateString(),
                'present' => $presentCount,
                'absent' => $totalEmployees - $presentCount,
            ];

            $current->addDay();
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_employees' => $totalEmployees,
                'present_today' => $presentToday,
                'absent_today' => $absentToday,
                'average_hours' => $avgHours,
            ],
            'recentCheckins' => $recentCheckins,
            'monthlyTrend' => $daysInMonth,
        ]);
    }
}
