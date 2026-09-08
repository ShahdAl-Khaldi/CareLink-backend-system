<?php

namespace Database\Factories;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
   public function definition(): array
{
    return [
        'doctor_id' => \App\Models\Doctor::inRandomOrder()->first()->id,
        'patient_id' => \App\Models\Patient::inRandomOrder()->first()->id,
        'scheduled_at' => fake()->dateTimeBetween('now', '+1 month'),
        'duration_minutes' => 30,
        'type' => fake()->randomElement(['online', 'in_person']),
        'status' => 'pending',
        'is_available' => true,
    ];
}
}
