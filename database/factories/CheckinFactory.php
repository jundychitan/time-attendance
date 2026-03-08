<?php

namespace Database\Factories;

use App\Models\Checkin;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Checkin>
 */
class CheckinFactory extends Factory
{
    protected $model = Checkin::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'latitude' => fake()->latitude(14.55, 14.60),
            'longitude' => fake()->longitude(120.98, 121.03),
            'location_name' => fake()->address(),
            'selfie_path' => 'selfies/placeholder.jpg',
            'captured_at' => now(),
        ];
    }

    /**
     * Set the checkin to a morning time (07:30–09:00).
     */
    public function morning(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'captured_at' => $date.' '.fake()->time('H:i:s', '09:00:00'),
        ])->state(function (array $attributes) use ($date) {
            $hour = fake()->numberBetween(7, 8);
            $minute = $hour === 7 ? fake()->numberBetween(30, 59) : fake()->numberBetween(0, 59);

            return [
                'captured_at' => "{$date} ".sprintf('%02d:%02d:%02d', $hour, $minute, fake()->numberBetween(0, 59)),
            ];
        });
    }

    /**
     * Set the checkin to an evening time (16:30–18:00).
     */
    public function evening(string $date): static
    {
        return $this->state(function (array $attributes) use ($date) {
            $hour = fake()->numberBetween(16, 17);
            $minute = $hour === 16 ? fake()->numberBetween(30, 59) : fake()->numberBetween(0, 59);

            return [
                'captured_at' => "{$date} ".sprintf('%02d:%02d:%02d', $hour, $minute, fake()->numberBetween(0, 59)),
            ];
        });
    }
}
