<?php

namespace Database\Seeders;

use App\Models\Emission;
use App\Models\EmissionFactor;
use App\Models\Production;
use Illuminate\Database\Seeder;

class EmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $production = Production::first();
        if (! $production) {
            return; // No productions to seed emissions for
        }

        // Get some emission factors
        $flightDomesticFactor = EmissionFactor::where('activity_code', 'flight_domestic')->first();
        $flightIntlFactor = EmissionFactor::where('activity_code', 'flight_international')->first();
        $hotelFactor = EmissionFactor::where('activity_code', 'accommodation_hotel')->first();
        $wasteFactor = EmissionFactor::where('activity_code', 'waste_landfill')->first();

        // Sample flight emissions
        if ($flightDomesticFactor) {
            Emission::create([
                'production_id' => $production->id,
                'record_date' => '2024-03-15',
                'record_period' => 202403,
                'department' => null,
                'activity_code' => 'flight_domestic',
                'scope' => 3,
                'country' => 'USA',
                'emission_factor_id' => $flightDomesticFactor->id,
                'calculation_version' => 'v2.1.0',
                'calculated_co2e' => 245.84, // 1850 km * 0.133 kg/km
                'record_flags' => 0,
                'data' => [
                    'flight_origin' => 'SFO',
                    'flight_destination' => 'LAX',
                    'flight_distance_km' => 1850,
                    'passengers' => 1,
                    'class' => 'economy',
                    'trip_purpose' => 'client_meeting',
                    'employee_id' => 'EMP001',
                ],
            ]);
        }

        if ($flightIntlFactor) {
            Emission::create([
                'production_id' => $production->id,
                'record_date' => '2024-04-22',
                'record_period' => 202404,
                'department' => null,
                'activity_code' => 'flight_international',
                'scope' => 3,
                'country' => 'USA',
                'emission_factor_id' => $flightIntlFactor->id,
                'calculation_version' => 'v2.1.0',
                'calculated_co2e' => 890.45, // 10005 km * 0.089 kg/km
                'record_flags' => 0,
                'data' => [
                    'flight_origin' => 'JFK',
                    'flight_destination' => 'LHR',
                    'flight_distance_km' => 10005,
                    'passengers' => 2,
                    'class' => 'economy',
                    'trip_purpose' => 'conference',
                    'employee_id' => 'EMP002',
                ],
            ]);
        }

        // Hotel accommodation
        if ($hotelFactor) {
            Emission::create([
                'production_id' => $production->id,
                'record_date' => '2024-04-23',
                'record_period' => 202404,
                'department' => null,
                'activity_code' => 'accommodation_hotel',
                'scope' => 3,
                'country' => 'GBR',
                'emission_factor_id' => $hotelFactor->id,
                'calculation_version' => 'v2.1.0',
                'calculated_co2e' => 86.1, // 3 nights * 28.7 kg/night
                'record_flags' => 0,
                'data' => [
                    'nights' => 3,
                    'room_type' => 'single',
                    'hotel_name' => 'London Business Hotel',
                    'city' => 'London',
                    'employee_id' => 'EMP002',
                ],
            ]);
        }

        // Waste emissions
        if ($wasteFactor) {
            Emission::create([
                'production_id' => $production->id,
                'record_date' => '2024-03-31',
                'record_period' => 202403,
                'department' => null,
                'activity_code' => 'waste_landfill',
                'scope' => 3,
                'country' => 'USA',
                'emission_factor_id' => $wasteFactor->id,
                'calculation_version' => 'v2.1.0',
                'calculated_co2e' => 260.0, // 500 kg * 0.52 kg/kg
                'record_flags' => 0,
                'data' => [
                    'waste_type' => 'mixed_municipal',
                    'amount' => 500,
                    'unit' => 'kg',
                    'disposal_method' => 'landfill',
                    'waste_contractor' => 'City Waste Management',
                ],
            ]);

            // Additional waste record to demonstrate variety
            Emission::create([
                'production_id' => $production->id,
                'record_date' => '2024-04-30',
                'record_period' => 202404,
                'department' => null,
                'activity_code' => 'waste_landfill',
                'scope' => 3,
                'country' => 'USA',
                'emission_factor_id' => $wasteFactor->id,
                'calculation_version' => 'v2.1.0',
                'calculated_co2e' => 364.0, // 700 kg * 0.52 kg/kg
                'record_flags' => 0,
                'data' => [
                    'waste_type' => 'mixed_municipal',
                    'amount' => 700,
                    'unit' => 'kg',
                    'disposal_method' => 'landfill',
                    'waste_contractor' => 'City Waste Management',
                ],
            ]);
        }

        // Add a few more flight records to demonstrate the generated columns
        if ($flightDomesticFactor) {
            Emission::create([
                'production_id' => $production->id,
                'record_date' => '2024-05-10',
                'record_period' => 202405,
                'department' => null,
                'activity_code' => 'flight_domestic',
                'scope' => 3,
                'country' => 'USA',
                'emission_factor_id' => $flightDomesticFactor->id,
                'calculation_version' => 'v2.1.0',
                'calculated_co2e' => 386.54, // 2906 km * 0.133 kg/km
                'record_flags' => 0,
                'data' => [
                    'flight_origin' => 'ORD',
                    'flight_destination' => 'SEA',
                    'flight_distance_km' => 2906,
                    'passengers' => 1,
                    'class' => 'business',
                    'trip_purpose' => 'sales_meeting',
                    'employee_id' => 'EMP003',
                ],
            ]);
        }
    }
}
