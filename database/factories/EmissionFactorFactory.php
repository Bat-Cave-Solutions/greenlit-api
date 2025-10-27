<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmissionFactor>
 */
class EmissionFactorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $activityCodes = [
            'flight_domestic',
            'flight_international',
            'accommodation_hotel',
            'waste_solid',
            'waste_liquid',
            'electricity_grid',
            'natural_gas',
            'vehicle_petrol',
            'vehicle_diesel',
        ];

        $sources = ['climatiq', 'defra', 'epa', 'ipcc'];
        $units = ['kg', 'tonne', 'litre', 'kwh', 'm3'];

        return [
            'activity_code' => $this->faker->randomElement($activityCodes),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'source' => $this->faker->randomElement($sources),
            'country' => $this->faker->countryCode(),
            'year' => $this->faker->numberBetween(2020, 2024),
            'unit' => $this->faker->randomElement($units),
            'co2_factor' => $this->faker->randomFloat(6, 0.001, 5.0),
            'ch4_factor' => $this->faker->randomFloat(6, 0.0001, 1.0),
            'n2o_factor' => $this->faker->randomFloat(6, 0.0001, 1.0),
            'co2e_factor' => $this->faker->randomFloat(6, 0.001, 10.0),
            'is_active' => true,
            'metadata' => json_encode([
                'scope' => $this->faker->numberBetween(1, 3),
                'category' => $this->faker->word(),
                'uncertainty' => $this->faker->randomFloat(2, 0.1, 0.5),
            ]),
        ];
    }

    /**
     * Factory state for flight emission factors
     */
    public function flight(): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_code' => 'flight_domestic',
            'name' => 'Domestic Flight - Economy Class',
            'co2e_factor' => $this->faker->randomFloat(6, 0.1, 0.3),
            'unit' => 'kg_km',
            'metadata' => json_encode([
                'scope' => 3,
                'category' => 'business_travel',
                'aircraft_type' => 'narrow_body',
            ]),
        ]);
    }

    /**
     * Factory state for accommodation emission factors
     */
    public function hotel(): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_code' => 'accommodation_hotel',
            'name' => 'Hotel Stay - Standard Room',
            'co2e_factor' => $this->faker->randomFloat(6, 20.0, 50.0),
            'unit' => 'kg_night',
            'metadata' => json_encode([
                'scope' => 3,
                'category' => 'business_travel',
                'room_type' => 'standard',
            ]),
        ]);
    }

    /**
     * Factory state for waste emission factors
     */
    public function waste(): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_code' => 'waste_solid',
            'name' => 'Solid Waste - Landfill',
            'co2e_factor' => $this->faker->randomFloat(6, 0.5, 2.0),
            'unit' => 'kg',
            'metadata' => json_encode([
                'scope' => 1,
                'category' => 'waste_disposal',
                'disposal_method' => 'landfill',
            ]),
        ]);
    }
}
