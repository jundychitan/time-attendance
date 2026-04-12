<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Support\CutoffPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            'email' => ['nullable', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $employee = Employee::query()->create($validated);

        $this->syncEmployeeUser($employee);

        return redirect("/employees/{$employee->id}")
            ->with('success', 'Employee created successfully.');
    }
    public function index(Request $request): Response
    {
        $query = Employee::query();

        // Company scope: admins only see their company's employees
        if ($request->user()->company) {
            $query->where('company', $request->user()->company);
        }

        $employees = $query
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
            'email' => ['nullable', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $employee->update($validated);

        $this->syncEmployeeUser($employee);

        return redirect("/employees/{$employee->id}")
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return redirect('/employees')
            ->with('success', 'Employee deleted successfully.');
    }

    /**
     * Create or update the linked user account for an employee.
     */
    private function syncEmployeeUser(Employee $employee): void
    {
        $username = $employee->email
            ?: strtolower(str_replace(' ', '', $employee->first_name).'.'.str_replace(' ', '', $employee->last_name));

        $user = User::query()->where('employee_id', $employee->id)->first();

        if ($user) {
            $user->update([
                'name' => $employee->full_name,
                'email' => $username,
                'company' => $employee->company,
            ]);
        } else {
            $defaultPassword = strtolower(
                str_replace(' ', '', $employee->first_name)
                .str_replace(' ', '', $employee->last_name)
                .'1234'
            );

            User::query()->create([
                'name' => $employee->full_name,
                'email' => $username,
                'password' => Hash::make($defaultPassword),
                'email_verified_at' => now(),
                'role' => 'employee',
                'employee_id' => $employee->id,
                'company' => $employee->company,
            ]);
        }
    }

    public function show(Employee $employee): Response
    {
        $employee->loadCount('checkins');

        $period = CutoffPeriod::current();
        $attendance = $employee->attendanceForRange(
            $period->start,
            $period->end,
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
