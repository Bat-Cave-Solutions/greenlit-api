<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Production;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample organizations
        $acmeCorp = Organization::create([
            'name' => 'ACME Corporation',
            'slug' => 'acme-corp',
            'description' => 'Sample manufacturing company for testing',
            'is_active' => true,
        ]);

        $greenTech = Organization::create([
            'name' => 'GreenTech Solutions',
            'slug' => 'greentech-solutions',
            'description' => 'Renewable energy consulting firm',
            'is_active' => true,
        ]);

        // Create sample productions
        Production::create([
            'name' => 'ACME 2024 Carbon Assessment',
            'description' => 'Full scope 1, 2, and 3 assessment for fiscal year 2024',
            'calculation_version' => 'v2.1.0',
            'organization_id' => $acmeCorp->id,
            'base_year' => '2023-01-01',
            'reporting_period_start' => '2024-01-01',
            'reporting_period_end' => '2024-12-31',
            'is_active' => true,
        ]);

        Production::create([
            'name' => 'GreenTech Q3 2024 Report',
            'description' => 'Quarterly emissions tracking',
            'calculation_version' => 'v2.1.0',
            'organization_id' => $greenTech->id,
            'base_year' => '2023-01-01',
            'reporting_period_start' => '2024-07-01',
            'reporting_period_end' => '2024-09-30',
            'is_active' => true,
        ]);
    }
}
