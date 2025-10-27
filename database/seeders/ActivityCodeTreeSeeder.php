<?php

namespace Database\Seeders;

use App\Models\ActivityCodeTree;
use Illuminate\Database\Seeder;

class ActivityCodeTreeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Scope 1 - Direct emissions
        $scope1 = ActivityCodeTree::create([
            'code' => 'scope_1',
            'name' => 'Scope 1 - Direct Emissions',
            'description' => 'Direct GHG emissions from sources owned or controlled by the organization',
            'level' => 0,
            'scope' => 1,
            'is_leaf' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        ActivityCodeTree::create([
            'code' => 'stationary_combustion',
            'name' => 'Stationary Combustion',
            'description' => 'Emissions from fuel combustion in stationary sources',
            'parent_code' => 'scope_1',
            'level' => 1,
            'scope' => 1,
            'unit' => 'liters',
            'is_leaf' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        ActivityCodeTree::create([
            'code' => 'mobile_combustion',
            'name' => 'Mobile Combustion',
            'description' => 'Emissions from fuel combustion in mobile sources',
            'parent_code' => 'scope_1',
            'level' => 1,
            'scope' => 1,
            'unit' => 'km',
            'is_leaf' => true,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Scope 3 - Indirect emissions
        $scope3 = ActivityCodeTree::create([
            'code' => 'scope_3',
            'name' => 'Scope 3 - Indirect Emissions',
            'description' => 'Indirect GHG emissions from value chain activities',
            'level' => 0,
            'scope' => 3,
            'is_leaf' => false,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        // Business travel category
        $businessTravel = ActivityCodeTree::create([
            'code' => 'business_travel',
            'name' => 'Category 6 - Business Travel',
            'description' => 'Emissions from business travel by employees',
            'parent_code' => 'scope_3',
            'level' => 1,
            'scope' => 3,
            'is_leaf' => false,
            'is_active' => true,
            'sort_order' => 6,
        ]);

        ActivityCodeTree::create([
            'code' => 'flight_domestic',
            'name' => 'Domestic Flights',
            'description' => 'Air travel within the same country',
            'parent_code' => 'business_travel',
            'level' => 2,
            'scope' => 3,
            'unit' => 'km',
            'is_leaf' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        ActivityCodeTree::create([
            'code' => 'flight_international',
            'name' => 'International Flights',
            'description' => 'Air travel between countries',
            'parent_code' => 'business_travel',
            'level' => 2,
            'scope' => 3,
            'unit' => 'km',
            'is_leaf' => true,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        ActivityCodeTree::create([
            'code' => 'accommodation_hotel',
            'name' => 'Hotel Accommodation',
            'description' => 'Emissions from hotel stays',
            'parent_code' => 'business_travel',
            'level' => 2,
            'scope' => 3,
            'unit' => 'nights',
            'is_leaf' => true,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        // Waste category
        $waste = ActivityCodeTree::create([
            'code' => 'waste_generated',
            'name' => 'Category 5 - Waste Generated',
            'description' => 'Emissions from waste disposal and treatment',
            'parent_code' => 'scope_3',
            'level' => 1,
            'scope' => 3,
            'is_leaf' => false,
            'is_active' => true,
            'sort_order' => 5,
        ]);

        ActivityCodeTree::create([
            'code' => 'waste_landfill',
            'name' => 'Landfill Waste',
            'description' => 'Waste sent to landfill',
            'parent_code' => 'waste_generated',
            'level' => 2,
            'scope' => 3,
            'unit' => 'kg',
            'is_leaf' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        ActivityCodeTree::create([
            'code' => 'waste_recycling',
            'name' => 'Recycled Waste',
            'description' => 'Waste sent for recycling',
            'parent_code' => 'waste_generated',
            'level' => 2,
            'scope' => 3,
            'unit' => 'kg',
            'is_leaf' => true,
            'is_active' => true,
            'sort_order' => 2,
        ]);
    }
}
