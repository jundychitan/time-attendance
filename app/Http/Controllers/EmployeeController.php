<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function index(Request $request): Response
    {
        $employees = Employee::query()
            ->search($request->input('search'))
            ->withCount('checkins')
            ->orderBy('last_name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('employees/Index', [
            'employees' => $employees,
            'filters' => $request->only('search'),
        ]);
    }

    public function show(Employee $employee): Response
    {
        $employee->loadCount('checkins');

        $attendance = $employee->attendanceForRange(
            now()->startOfMonth(),
            now(),
        );

        $recentCheckins = $employee->checkins()
            ->orderByDesc('captured_at')
            ->limit(20)
            ->get();

        return Inertia::render('employees/Show', [
            'employee' => $employee,
            'attendance' => $attendance,
            'recentCheckins' => $recentCheckins,
        ]);
    }
}
