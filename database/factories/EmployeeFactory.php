<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_number' => 'EMP-'.fake()->unique()->numerify('###'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'department' => fake()->randomElement(['Engineering', 'HR', 'Finance', 'Operations', 'Marketing']),
            'position' => fake()->jobTitle(),
            'is_active' => true,
        ];
    }

    /**
     * Mark the employee as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
