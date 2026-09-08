<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\PatientProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PatientProfile>
 */
class PatientProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'blood_type' => fake()->randomElement(['A+', 'O+', 'B-']),
            'weight_kg' => fake()->numberBetween(50, 110),
            'height_cm' => fake()->numberBetween(150, 190),
            'is_diabetic' => fake()->boolean(),
            'is_smoker' => fake()->boolean(),
        ];
    }
}
