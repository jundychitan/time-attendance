<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('employees/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_number' => ['required', 'string', 'max:255', 'unique:employees,id_number'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $employee = Employee::query()->create($validated);

        return redirect("/employees/{$employee->id}")
            ->with('success', 'Employee created successfully.');
    }
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

    public function edit(Employee $employee): Response
    {
        return Inertia::render('employees/Edit', [
            'employee' => $employee,
        ]);
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'id_number' => ['required', 'string', 'max:255', 'unique:employees,id_number,'.$employee->id],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $employee->update($validated);

        return redirect("/employees/{$employee->id}")
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return redirect('/employees')
            ->with('success', 'Employee deleted successfully.');
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
