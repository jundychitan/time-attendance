<?php

use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CheckinController;
use App\Http\Controllers\Api\V1\EmployeeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Auth (public)
    Route::post('auth/token', [AuthController::class, 'store']);

    // Mobile app checkin endpoint (API key auth + logging)
    Route::middleware(['api.log', 'api.key'])->group(function () {
        Route::post('checkins', [CheckinController::class, 'store']);
    });

    // Protected routes (Sanctum token auth)
    Route::middleware('auth:sanctum')->group(function () {
        Route::delete('auth/token', [AuthController::class, 'destroy']);

        Route::apiResource('employees', EmployeeController::class);
        Route::get('employees/{employee}/attendance', [EmployeeController::class, 'attendance']);

        Route::get('checkins', [CheckinController::class, 'index']);

        Route::get('attendance', [AttendanceController::class, 'index']);
        Route::get('attendance/summary', [AttendanceController::class, 'summary']);
    });
});
