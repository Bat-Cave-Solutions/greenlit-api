<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $companyName = $this->faker->company();

        return [
            'name' => $companyName,
            'slug' => strtolower(str_replace([' ', '.', ','], ['-', '', ''], $companyName)),
            'description' => $this->faker->paragraph(),
            'is_active' => true,
        ];
    }
}
