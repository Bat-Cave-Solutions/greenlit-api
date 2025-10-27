<?php

namespace Database\Factories;

use App\Models\EmissionFactor;
use App\Models\Production;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Emission>
 */
class EmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $recordDate = fake()->dateTimeBetween('-1 year', 'now');
        $activityCodes = ['flight_domestic', 'flight_international', 'accommodation_hotel', 'waste_landfill'];
        $activityCode = fake()->randomElement($activityCodes);

        return [
            'production_id' => Production::factory(),
            'record_date' => $recordDate,
            'record_period' => (int) $recordDate->format('Ym'),
            'department' => fake()->optional()->numberBetween(1, 10),
            'activity_code' => $activityCode,
            'scope' => fake()->randomElement([1, 2, 3]),
            'country' => fake()->countryISOAlpha3(),
            'emission_factor_id' => EmissionFactor::factory(),
            'custom_factor_id' => null,
            'calculation_version' => 'v2.1.0',
            'calculated_co2e' => fake()->randomFloat(2, 10, 1000),
            'record_flags' => 0,
            'data' => $this->generateActivityData($activityCode),
        ];
    }

    /**
     * Generate category-specific data based on activity code.
     */
    private function generateActivityData(string $activityCode): array
    {
        return match ($activityCode) {
            'flight_domestic', 'flight_international' => [
                'flight_origin' => fake()->lexify('???'),
                'flight_destination' => fake()->lexify('???'),
                'flight_distance_km' => fake()->numberBetween(500, 15000),
                'passengers' => fake()->numberBetween(1, 4),
                'class' => fake()->randomElement(['economy', 'business', 'first']),
                'trip_purpose' => fake()->randomElement(['business', 'conference', 'client_meeting']),
                'employee_id' => 'EMP'.fake()->numberBetween(1000, 9999),
            ],
            'accommodation_hotel' => [
                'nights' => fake()->numberBetween(1, 7),
                'room_type' => fake()->randomElement(['single', 'double', 'suite']),
                'hotel_name' => fake()->company().' Hotel',
                'city' => fake()->city(),
                'employee_id' => 'EMP'.fake()->numberBetween(1000, 9999),
            ],
            'waste_landfill' => [
                'waste_type' => fake()->randomElement(['mixed_municipal', 'organic', 'paper', 'plastic']),
                'amount' => fake()->numberBetween(50, 1000),
                'unit' => 'kg',
                'disposal_method' => 'landfill',
                'waste_contractor' => fake()->company().' Waste Services',
            ],
            default => [
                'amount' => fake()->numberBetween(10, 500),
                'unit' => fake()->randomElement(['kg', 'liters', 'kwh', 'km']),
                'description' => fake()->sentence(),
            ],
        };
    }

    /**
     * Create a flight emission with specific data.
     */
    public function flight(): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_code' => fake()->randomElement(['flight_domestic', 'flight_international']),
            'scope' => 3,
            'data' => [
                'flight_origin' => fake()->lexify('???'),
                'flight_destination' => fake()->lexify('???'),
                'flight_distance_km' => fake()->numberBetween(500, 15000),
                'passengers' => fake()->numberBetween(1, 4),
                'class' => fake()->randomElement(['economy', 'business', 'first']),
                'trip_purpose' => fake()->randomElement(['business', 'conference', 'client_meeting']),
                'employee_id' => 'EMP'.fake()->numberBetween(1000, 9999),
            ],
        ]);
    }

    /**
     * Create a hotel accommodation emission.
     */
    public function hotel(): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_code' => 'accommodation_hotel',
            'scope' => 3,
            'data' => [
                'nights' => fake()->numberBetween(1, 7),
                'room_type' => fake()->randomElement(['single', 'double', 'suite']),
                'location' => fake()->city().', '.fake()->countryCode(),
                'hotel_star_rating' => fake()->numberBetween(1, 5),
                'hotel_name' => fake()->company().' Hotel',
                'employee_id' => 'EMP'.fake()->numberBetween(1000, 9999),
            ],
        ]);
    }

    /**
     * Create a waste emission.
     */
    public function waste(): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_code' => 'waste_landfill',
            'scope' => 3,
            'data' => [
                'waste_type' => fake()->randomElement(['mixed_municipal', 'organic', 'paper', 'plastic']),
                'amount' => fake()->numberBetween(50, 1000),
                'quantity_kg' => fake()->numberBetween(50, 1000),
                'disposal_method' => 'landfill',
                'location' => fake()->city().', '.fake()->countryCode(),
                'waste_contractor' => fake()->company().' Waste Services',
            ],
        ]);
    }
}
