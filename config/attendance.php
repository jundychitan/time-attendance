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

];
