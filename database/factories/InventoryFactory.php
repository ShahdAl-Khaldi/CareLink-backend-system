<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'category' => $this->faker->word(),
            'company' => $this->faker->company(),
            'quantity' => $this->faker->numberBetween(10, 100),
            'min_quantity' => 10,
            'unit' => 'علبة',
            'price' => $this->faker->randomFloat(2, 5, 200),
            'expiry_date' => $this->faker->dateTimeBetween('+1 month', '+2 years')->format('Y-m-d'),
            'keywords' => null,
            'updated_by' => null,
        ];
    }
}