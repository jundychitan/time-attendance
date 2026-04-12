<?php

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Models\Employee;
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
        $date = Carbon::parse($request->input('date', now()->toDateString()));

        $employees = Employee::query()->active()->orderBy('last_name')->get();

        $attendance = $employees->map(function (Employee $employee) use ($date) {
            $record = $employee->attendanceForDate($date);

            // Get selfies based on actual checkins used
            $selfieInUrl = null;
            $selfieOutUrl = null;

            if ($record['checkin_id']) {
                $firstCheckin = Checkin::find($record['checkin_id']);
                if ($firstCheckin) {
                    $selfieInUrl = Storage::disk('s3')->url($firstCheckin->selfie_path);
                }
            }

            // Find the time-out checkin for selfie
            if ($record['time_out'] && ! $record['manual_time_out']) {
                $checkins = $employee->checkins()
                    ->whereDate('captured_at', $record['time_out_next_day'] ? $date->copy()->addDay() : $date)
                    ->orderByDesc('captured_at')
                    ->first();

                if ($checkins) {
                    $selfieOutUrl = Storage::disk('s3')->url($checkins->selfie_path);
                }
            }

            return [
                'employee' => [
                    'id' => $employee->id,
                    'id_number' => $employee->id_number,
                    'full_name' => $employee->full_name,
                    'department' => $employee->department,
                ],
                ...$record,
                'selfie_in_url' => $selfieInUrl,
                'selfie_out_url' => $selfieOutUrl,
            ];
        });

        return Inertia::render('attendance/Index', [
            'attendance' => $attendance,
            'date' => $date->toDateString(),
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
