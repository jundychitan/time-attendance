<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function index(Request $request): Response
    {
        $date = Carbon::parse($request->input('date', now()->toDateString()));

        $employees = Employee::query()->active()->orderBy('last_name')->get();

        $attendance = $employees->map(function (Employee $employee) use ($date) {
            $record = $employee->attendanceForDate($date);

            return [
                'employee' => [
                    'id' => $employee->id,
                    'id_number' => $employee->id_number,
                    'full_name' => $employee->full_name,
                    'department' => $employee->department,
                ],
                ...$record,
            ];
        });

        return Inertia::render('attendance/Index', [
            'attendance' => $attendance,
            'date' => $date->toDateString(),
        ]);
    }
}
