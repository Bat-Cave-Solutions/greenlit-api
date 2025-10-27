<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Production>
 */
class ProductionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => \App\Models\Organization::factory(),
            'name' => 'Assessment '.$this->faker->year().' - '.$this->faker->word(),
            'description' => $this->faker->paragraph(),
            'calculation_version' => $this->faker->randomElement(['v1.0.0', 'v2.0.0', 'v2.1.0']),
            'base_year' => $this->faker->numberBetween(2020, 2024),
            'reporting_period_start' => $this->faker->dateTimeBetween('-2 years', '-1 year'),
            'reporting_period_end' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'is_active' => true,
        ];
    }
}
