<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomEmissionFactor>
 */
class CustomEmissionFactorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $activityCodes = [
            'custom_flight',
            'custom_vehicle',
            'custom_energy',
            'custom_waste',
            'custom_material',
        ];

        $units = ['kg', 'tonne', 'litre', 'kwh', 'm3'];

        return [
            'organization_id' => Organization::factory(),
            'activity_code' => $this->faker->randomElement($activityCodes),
            'name' => 'Custom '.$this->faker->sentence(2),
            'description' => $this->faker->paragraph(),
            'unit' => $this->faker->randomElement($units),
            'co2_factor' => $this->faker->randomFloat(6, 0.001, 8.0),
            'ch4_factor' => $this->faker->randomFloat(6, 0.0001, 2.0),
            'n2o_factor' => $this->faker->randomFloat(6, 0.0001, 2.0),
            'co2e_factor' => $this->faker->randomFloat(6, 0.001, 15.0),
            'is_active' => true,
            'metadata' => json_encode([
                'scope' => $this->faker->numberBetween(1, 3),
                'category' => $this->faker->word(),
                'calculation_method' => $this->faker->randomElement(['measured', 'estimated', 'calculated']),
                'methodology' => $this->faker->sentence(),
                'source_reference' => $this->faker->sentence(),
                'uncertainty_range' => [
                    'min' => $this->faker->randomFloat(2, 0.1, 0.3),
                    'max' => $this->faker->randomFloat(2, 0.4, 0.8),
                ],
            ]),
        ];
    }

    /**
     * Factory state for custom flight factors
     */
    public function flight(): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_code' => 'custom_flight',
            'name' => 'Custom Corporate Fleet Flight Factor',
            'co2e_factor' => $this->faker->randomFloat(6, 0.2, 0.4),
            'unit' => 'kg_km',
            'metadata' => json_encode([
                'scope' => 3,
                'category' => 'business_travel',
                'fleet_type' => 'corporate_jet',
                'methodology' => 'Based on actual fuel consumption data from corporate fleet',
                'data_period' => '2023-2024',
            ]),
        ]);
    }

    /**
     * Factory state for custom energy factors
     */
    public function energy(): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_code' => 'custom_energy',
            'name' => 'Custom Facility Energy Factor',
            'co2e_factor' => $this->faker->randomFloat(6, 0.3, 0.8),
            'unit' => 'kwh',
            'metadata' => json_encode([
                'scope' => 2,
                'category' => 'purchased_electricity',
                'methodology' => 'Location-based calculation using local grid mix data',
                'grid_mix' => 'local_utility',
                'renewable_percentage' => $this->faker->numberBetween(10, 60),
            ]),
        ]);
    }

    /**
     * Factory state for custom waste factors
     */
    public function waste(): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_code' => 'custom_waste',
            'name' => 'Custom Waste Processing Factor',
            'co2e_factor' => $this->faker->randomFloat(6, 1.0, 3.0),
            'unit' => 'kg',
            'metadata' => json_encode([
                'scope' => 1,
                'category' => 'waste_treatment',
                'methodology' => 'Site-specific waste processing and disposal tracking',
                'treatment_method' => $this->faker->randomElement(['recycling', 'composting', 'incineration']),
                'facility_efficiency' => $this->faker->randomFloat(2, 0.7, 0.95),
            ]),
        ]);
    }
}
