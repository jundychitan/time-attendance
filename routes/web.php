<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ApiLogController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeePortalController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Role-based home redirect
    Route::get('home', function () {
        return auth()->user()->isEmployee()
            ? redirect('/my-attendance')
            : redirect('/dashboard');
    })->name('home.redirect');

    // Employee self-service (accessible by all authenticated users)
    Route::get('my-attendance', [EmployeePortalController::class, 'index'])->name('my-attendance');

    // Admin routes (restricted from employee-role users)
    Route::middleware('admin')->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::patch('attendance/checkins/{checkin}/manual-time-out', [AttendanceController::class, 'updateManualTimeOut'])->name('attendance.manual-time-out');
    Route::patch('attendance/checkins/{checkin}/approve', [AttendanceController::class, 'approveManualTimeOut'])->name('attendance.approve');
    Route::patch('attendance/checkins/{checkin}/reject', [AttendanceController::class, 'rejectManualTimeOut'])->name('attendance.reject');

    Route::get('api-logs', [ApiLogController::class, 'index'])->name('api-logs.index');
    }); // end admin middleware

    Route::middleware('super.admin')->group(function () {
        Route::get('admin-users', [AdminUserController::class, 'index'])->name('admin-users.index');
        Route::get('admin-users/create', [AdminUserController::class, 'create'])->name('admin-users.create');
        Route::post('admin-users', [AdminUserController::class, 'store'])->name('admin-users.store');
        Route::get('admin-users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin-users.edit');
        Route::put('admin-users/{user}', [AdminUserController::class, 'update'])->name('admin-users.update');
        Route::delete('admin-users/{user}', [AdminUserController::class, 'destroy'])->name('admin-users.destroy');
    });
});

require __DIR__.'/settings.php';
