<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreEmployeeRequest;
use App\Http\Requests\Api\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EmployeeController extends Controller
{
    /**
     * List employees with optional search and pagination.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $employees = Employee::query()
            ->search($request->input('search'))
            ->when($request->boolean('active_only'), fn ($q) => $q->active())
            ->orderBy('last_name')
            ->paginate($request->input('per_page', 15));

        return EmployeeResource::collection($employees);
    }

    /**
     * Create a new employee.
     */
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = Employee::query()->create($request->validated());

        return (new EmployeeResource($employee))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Show a single employee.
     */
    public function show(Employee $employee): EmployeeResource
    {
        return new EmployeeResource($employee);
    }

    /**
     * Update an employee.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): EmployeeResource
    {
        $employee->update($request->validated());

        return new EmployeeResource($employee);
    }

    /**
     * Deactivate an employee.
     */
    public function destroy(Employee $employee): JsonResponse
    {
        $employee->update(['is_active' => false]);

        return response()->json(['message' => 'Employee deactivated.']);
    }

    /**
     * Get attendance records for a specific employee.
     */
    public function attendance(Request $request, Employee $employee): JsonResponse
    {
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $attendance = $employee->attendanceForRange(
            $request->input('start_date'),
            $request->input('end_date'),
        );

        return response()->json(['data' => $attendance]);
    }
}
