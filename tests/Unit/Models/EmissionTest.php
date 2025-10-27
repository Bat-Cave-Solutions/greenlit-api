<?php

namespace Tests\Unit\Models;

use App\Models\CustomEmissionFactor;
use App\Models\Emission;
use App\Models\EmissionFactor;
use App\Models\Organization;
use App\Models\Production;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmissionTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected Production $production;

    protected EmissionFactor $emissionFactor;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up test data
        $this->organization = Organization::factory()->create();
        $this->production = Production::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $this->emissionFactor = EmissionFactor::factory()->create();
    }

    public function test_emission_can_be_created_with_factory()
    {
        $emission = Emission::factory()->create([
            'production_id' => $this->production->id,
            'emission_factor_id' => $this->emissionFactor->id,
        ]);

        $this->assertDatabaseHas('emissions', [
            'id' => $emission->id,
            'production_id' => $this->production->id,
        ]);
    }

    public function test_emission_belongs_to_production()
    {
        $emission = Emission::factory()->create([
            'production_id' => $this->production->id,
            'emission_factor_id' => $this->emissionFactor->id,
        ]);

        $this->assertInstanceOf(Production::class, $emission->production);
        $this->assertEquals($this->production->id, $emission->production->id);
    }

    public function test_emission_belongs_to_emission_factor()
    {
        $emission = Emission::factory()->create([
            'production_id' => $this->production->id,
            'emission_factor_id' => $this->emissionFactor->id,
        ]);

        $this->assertInstanceOf(EmissionFactor::class, $emission->emissionFactor);
        $this->assertEquals($this->emissionFactor->id, $emission->emissionFactor->id);
    }

    public function test_emission_can_belong_to_custom_emission_factor()
    {
        $customFactor = CustomEmissionFactor::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $emission = Emission::factory()->create([
            'production_id' => $this->production->id,
            'custom_factor_id' => $customFactor->id,
            'emission_factor_id' => null,
        ]);

        $this->assertInstanceOf(CustomEmissionFactor::class, $emission->customEmissionFactor);
        $this->assertEquals($customFactor->id, $emission->customEmissionFactor->id);
    }

    public function test_emission_has_json_data_accessor()
    {
        $emission = Emission::factory()->flight()->create([
            'production_id' => $this->production->id,
            'emission_factor_id' => $this->emissionFactor->id,
        ]);

        $this->assertIsArray($emission->data);
        $this->assertArrayHasKey('flight_origin', $emission->data);
        $this->assertIsString($emission->data['flight_origin']);
    }

    public function test_flight_factory_generates_correct_data_structure()
    {
        $emission = Emission::factory()->flight()->create([
            'production_id' => $this->production->id,
            'emission_factor_id' => $this->emissionFactor->id,
        ]);

        $this->assertArrayHasKey('flight_origin', $emission->data);
        $this->assertArrayHasKey('flight_destination', $emission->data);
        $this->assertArrayHasKey('flight_distance_km', $emission->data);
        $this->assertArrayHasKey('passengers', $emission->data);
        $this->assertArrayHasKey('class', $emission->data);
        $this->assertArrayHasKey('trip_purpose', $emission->data);

        // Validate data types
        $this->assertIsString($emission->data['flight_origin']);
        $this->assertIsString($emission->data['flight_destination']);
        $this->assertIsNumeric($emission->data['flight_distance_km']);
        $this->assertIsInt($emission->data['passengers']);
    }

    public function test_hotel_factory_generates_correct_data_structure()
    {
        $emission = Emission::factory()->hotel()->create([
            'production_id' => $this->production->id,
            'emission_factor_id' => $this->emissionFactor->id,
        ]);

        $this->assertArrayHasKey('nights', $emission->data);
        $this->assertArrayHasKey('room_type', $emission->data);
        $this->assertArrayHasKey('location', $emission->data);
        $this->assertArrayHasKey('hotel_star_rating', $emission->data);

        // Validate data types
        $this->assertIsInt($emission->data['nights']);
        $this->assertIsString($emission->data['room_type']);
        $this->assertIsInt($emission->data['hotel_star_rating']);
        $this->assertGreaterThanOrEqual(1, $emission->data['hotel_star_rating']);
        $this->assertLessThanOrEqual(5, $emission->data['hotel_star_rating']);
    }

    public function test_waste_factory_generates_correct_data_structure()
    {
        $emission = Emission::factory()->waste()->create([
            'production_id' => $this->production->id,
            'emission_factor_id' => $this->emissionFactor->id,
        ]);

        $this->assertArrayHasKey('waste_type', $emission->data);
        $this->assertArrayHasKey('amount', $emission->data);
        $this->assertArrayHasKey('quantity_kg', $emission->data);
        $this->assertArrayHasKey('disposal_method', $emission->data);
        $this->assertArrayHasKey('location', $emission->data);

        // Validate data types
        $this->assertIsString($emission->data['waste_type']);
        $this->assertIsNumeric($emission->data['amount']);
        $this->assertIsNumeric($emission->data['quantity_kg']);
        $this->assertIsString($emission->data['disposal_method']);
    }

    public function test_scope_validation_accepts_valid_values()
    {
        $validScopes = [1, 2, 3];

        foreach ($validScopes as $scope) {
            $emission = Emission::factory()->make([
                'production_id' => $this->production->id,
                'emission_factor_id' => $this->emissionFactor->id,
                'scope' => $scope,
            ]);

            $this->assertTrue($emission->save());
        }
    }

    public function test_record_period_format_validation()
    {
        // Valid format (YYYYMM)
        $emission = Emission::factory()->create([
            'production_id' => $this->production->id,
            'emission_factor_id' => $this->emissionFactor->id,
            'record_period' => 202410,
        ]);

        $this->assertDatabaseHas('emissions', [
            'id' => $emission->id,
            'record_period' => 202410,
        ]);
    }

    public function test_country_code_validation()
    {
        // Valid 3-letter country code
        $emission = Emission::factory()->create([
            'production_id' => $this->production->id,
            'emission_factor_id' => $this->emissionFactor->id,
            'country' => 'USA',
        ]);

        $this->assertDatabaseHas('emissions', [
            'id' => $emission->id,
            'country' => 'USA',
        ]);
    }

    public function test_either_emission_factor_or_custom_factor_required()
    {
        // Should work with emission_factor_id
        $emission1 = Emission::factory()->create([
            'production_id' => $this->production->id,
            'emission_factor_id' => $this->emissionFactor->id,
            'custom_factor_id' => null,
        ]);
        $this->assertNotNull($emission1->id);

        // Should work with custom_factor_id
        $customFactor = CustomEmissionFactor::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $emission2 = Emission::factory()->create([
            'production_id' => $this->production->id,
            'emission_factor_id' => null,
            'custom_factor_id' => $customFactor->id,
        ]);
        $this->assertNotNull($emission2->id);
    }

    public function test_calculation_version_is_stored()
    {
        $emission = Emission::factory()->create([
            'production_id' => $this->production->id,
            'emission_factor_id' => $this->emissionFactor->id,
            'calculation_version' => 'v2.1.0',
        ]);

        $this->assertEquals('v2.1.0', $emission->calculation_version);
    }

    public function test_record_flags_defaults_to_zero()
    {
        $emission = Emission::factory()->create([
            'production_id' => $this->production->id,
            'emission_factor_id' => $this->emissionFactor->id,
        ]);

        $this->assertEquals(0, $emission->record_flags);
    }
}
