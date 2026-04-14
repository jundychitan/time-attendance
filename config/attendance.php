<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payroll Cutoff Day
    |--------------------------------------------------------------------------
    |
    | The day of the month that splits attendance into two cutoff periods.
    | Period 1: 1st to cutoff_day (e.g. Apr 1 - Apr 15)
    | Period 2: cutoff_day + 1 to end of month (e.g. Apr 16 - Apr 30)
    |
    */

    'cutoff_day' => (int) env('ATTENDANCE_CUTOFF_DAY', 15),

    /*
    |--------------------------------------------------------------------------
    | Overtime Rules
    |--------------------------------------------------------------------------
    */

    'regular_shift_hours' => (int) env('ATTENDANCE_REGULAR_SHIFT_HOURS', 9),
    'break_hours' => (float) env('ATTENDANCE_BREAK_HOURS', 1),
    'regular_buffer_minutes' => (int) env('ATTENDANCE_REGULAR_BUFFER_MINUTES', 30),
    'overtime_buffer_minutes' => (int) env('ATTENDANCE_OVERTIME_BUFFER_MINUTES', 30),

];
