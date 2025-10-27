<?php

namespace Database\Seeders;

use App\Models\EmissionFactor;
use Illuminate\Database\Seeder;

class EmissionFactorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Flight emission factors
        EmissionFactor::create([
            'activity_code' => 'flight_domestic',
            'name' => 'Domestic Flight - Economy Class',
            'description' => 'Economy class domestic flights per passenger-km',
            'source' => 'climatiq',
            'country' => 'USA',
            'year' => 2024,
            'unit' => 'km',
            'co2_factor' => 0.133,
            'ch4_factor' => 0.000001,
            'n2o_factor' => 0.000002,
            'co2e_factor' => 0.133,
            'metadata' => [
                'category' => 'passenger_flight',
                'class' => 'economy',
                'flight_type' => 'domestic',
            ],
            'is_active' => true,
        ]);

        EmissionFactor::create([
            'activity_code' => 'flight_international',
            'name' => 'International Flight - Economy Class',
            'description' => 'Economy class international flights per passenger-km',
            'source' => 'climatiq',
            'country' => 'USA',
            'year' => 2024,
            'unit' => 'km',
            'co2_factor' => 0.089,
            'ch4_factor' => 0.000001,
            'n2o_factor' => 0.000001,
            'co2e_factor' => 0.089,
            'metadata' => [
                'category' => 'passenger_flight',
                'class' => 'economy',
                'flight_type' => 'international',
            ],
            'is_active' => true,
        ]);

        // Hotel accommodation
        EmissionFactor::create([
            'activity_code' => 'accommodation_hotel',
            'name' => 'Hotel Accommodation',
            'description' => 'Hotel room occupancy per night',
            'source' => 'defra',
            'country' => 'USA',
            'year' => 2024,
            'unit' => 'nights',
            'co2_factor' => 28.4,
            'ch4_factor' => 0.01,
            'n2o_factor' => 0.02,
            'co2e_factor' => 28.7,
            'metadata' => [
                'category' => 'accommodation',
                'type' => 'hotel',
            ],
            'is_active' => true,
        ]);

        // Waste factors
        EmissionFactor::create([
            'activity_code' => 'waste_landfill',
            'name' => 'Municipal Solid Waste - Landfill',
            'description' => 'Municipal solid waste disposed in landfill',
            'source' => 'epa',
            'country' => 'USA',
            'year' => 2024,
            'unit' => 'kg',
            'co2_factor' => 0.45,
            'ch4_factor' => 0.82,
            'n2o_factor' => 0.003,
            'co2e_factor' => 0.52,
            'metadata' => [
                'category' => 'waste',
                'disposal_method' => 'landfill',
                'waste_type' => 'municipal_solid',
            ],
            'is_active' => true,
        ]);

        EmissionFactor::create([
            'activity_code' => 'waste_recycling',
            'name' => 'Municipal Solid Waste - Recycling',
            'description' => 'Municipal solid waste sent for recycling',
            'source' => 'epa',
            'country' => 'USA',
            'year' => 2024,
            'unit' => 'kg',
            'co2_factor' => -0.12,
            'ch4_factor' => 0.0,
            'n2o_factor' => 0.0,
            'co2e_factor' => -0.12,
            'metadata' => [
                'category' => 'waste',
                'disposal_method' => 'recycling',
                'waste_type' => 'municipal_solid',
            ],
            'is_active' => true,
        ]);

        // Stationary combustion
        EmissionFactor::create([
            'activity_code' => 'stationary_combustion',
            'name' => 'Natural Gas Combustion',
            'description' => 'Natural gas combustion in stationary sources',
            'source' => 'epa',
            'country' => 'USA',
            'year' => 2024,
            'unit' => 'liters',
            'co2_factor' => 1.93,
            'ch4_factor' => 0.000038,
            'n2o_factor' => 0.000036,
            'co2e_factor' => 1.94,
            'metadata' => [
                'category' => 'stationary_combustion',
                'fuel_type' => 'natural_gas',
            ],
            'is_active' => true,
        ]);
    }
}
