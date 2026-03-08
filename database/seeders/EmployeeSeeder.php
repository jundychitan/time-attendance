<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            [
                'id_number' => 'EMP-001',
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'department' => 'Engineering',
                'position' => 'Senior Developer',
            ],
            [
                'id_number' => 'EMP-002',
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'department' => 'HR',
                'position' => 'HR Manager',
            ],
            [
                'id_number' => 'EMP-003',
                'first_name' => 'Jose',
                'last_name' => 'Reyes',
                'department' => 'Finance',
                'position' => 'Accountant',
            ],
        ];

        foreach ($employees as $employee) {
            Employee::query()->create($employee);
        }
    }
}
