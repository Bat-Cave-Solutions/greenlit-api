<?php

namespace Tests\Feature\Database;

use App\Models\CustomEmissionFactor;
use App\Models\Emission;
use App\Models\EmissionFactor;
use App\Models\Organization;
use App\Models\Production;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EmissionsDatabaseTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected Production $production;

    protected EmissionFactor $emissionFactor;

    protected function setUp(): void
    {
        parent::setUp();

        // These tests rely on PostgreSQL features (JSONB, generated columns, GIN indexes).
        // Skip the entire suite when not running against a Postgres driver (e.g., SQLite in CI).
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('PostgreSQL-only database tests are skipped on non-PgSQL drivers.');
        }

        $this->organization = Organization::factory()->create();
        $this->production = Production::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $this->emissionFactor = EmissionFactor::factory()->create();
    }

    public function test_generated_columns_are_created_correctly()
    {
        // Insert emission with flight data using flight factory
        $emission = Emission::factory()->flight()->create([
            'production_id' => $this->production->id,
            'emission_factor_id' => $this->emissionFactor->id,
            'data' => [
                'flight_origin' => 'SFO',
                'flight_destination' => 'LAX',
                'flight_distance_km' => 1850,
                'passengers' => 1,
                'class' => 'economy',
            ],
        ]);

        // Refresh from database to get generated column values
        $emission->refresh();

        // Test that generated columns are populated correctly
        $this->assertEquals('SFO', $emission->flight_origin);
        $this->assertEquals('LAX', $emission->flight_destination);
        $this->assertEquals('1850.00', $emission->flight_distance_km);
    }

    public function test_generated_columns_handle_null_json_data()
    {
        // Create emission without flight data (use waste factory for valid waste data)
        $emission = Emission::factory()->waste()->create([
            'production_id' => $this->production->id,
            'emission_factor_id' => $this->emissionFactor->id,
        ]);

        $emission->refresh();

        // Generated columns should be null when JSON keys don't exist
        $this->assertNull($emission->flight_origin);
        $this->assertNull($emission->flight_destination);
        $this->assertNull($emission->flight_distance_km);
    }

    public function test_generated_columns_can_be_queried_with_indexes()
    {
        // Create multiple flight emissions with different origins using flight factory
        Emission::factory()->flight()->create([
            'production_id' => $this->production->id,
            'emission_factor_id' => $this->emissionFactor->id,
            'data' => ['flight_origin' => 'SFO', 'flight_destination' => 'LAX', 'passengers' => 1, 'class' => 'economy'],
        ]);

        Emission::factory()->flight()->create([
            'production_id' => $this->production->id,
            'emission_factor_id' => $this->emissionFactor->id,
            'data' => ['flight_origin' => 'JFK', 'flight_destination' => 'LHR', 'passengers' => 1, 'class' => 'business'],
        ]);

        Emission::factory()->flight()->create([
            'production_id' => $this->production->id,
            'emission_factor_id' => $this->emissionFactor->id,
            'data' => ['flight_origin' => 'SFO', 'flight_destination' => 'ORD', 'passengers' => 2, 'class' => 'economy'],
        ]);

        // Test querying by generated columns (should use B-tree index)
        $sfoFlights = Emission::where('flight_origin', 'SFO')->count();
        $jfkFlights = Emission::where('flight_origin', 'JFK')->count();

        $this->assertEquals(2, $sfoFlights);
        $this->assertEquals(1, $jfkFlights);

        // Test composite route queries
        $sfxToLax = Emission::where('flight_origin', 'SFO')
            ->where('flight_destination', 'LAX')
            ->count();
        $this->assertEquals(1, $sfxToLax);
    }

    public function test_jsonb_queries_work_with_gin_index()
    {
        // Create flight emissions with different JSON data
        Emission::factory()->flight()->create([
            'production_id' => $this->production->id,
            'emission_factor_id' => $this->emissionFactor->id,
            'data' => [
                'flight_origin' => 'SFO',
                'flight_destination' => 'LAX',
                'class' => 'business',
                'trip_purpose' => 'client_meeting',
            ],
        ]);

        Emission::factory()->flight()->create([
            'production_id' => $this->production->id,
            'emission_factor_id' => $this->emissionFactor->id,
            'data' => [
                'flight_origin' => 'JFK',
                'flight_destination' => 'LHR',
                'class' => 'economy',
                'trip_purpose' => 'business_meeting',
            ],
        ]);

        Emission::factory()->flight()->create([
            'production_id' => $this->production->id,
            'emission_factor_id' => $this->emissionFactor->id,
            'data' => [
                'flight_origin' => 'LAX',
                'flight_destination' => 'ORD',
                'class' => 'business',
                'trip_purpose' => 'conference',
            ],
        ]);

        // Test JSONB containment queries (should use GIN index)
        $businessFlights = Emission::whereJsonContains('data', ['class' => 'business'])->count();
        $economyFlights = Emission::whereJsonContains('data', ['class' => 'economy'])->count();

        $this->assertEquals(2, $businessFlights);
        $this->assertEquals(1, $economyFlights);

        // Test JSONB path queries
        $clientMeetings = Emission::whereRaw("data->>'trip_purpose' = ?", ['client_meeting'])->count();
        $this->assertEquals(1, $clientMeetings);
    }

    public function test_scope_check_constraint_validation()
    {
        // Valid scopes should work
        $validScopes = [1, 2, 3];
        foreach ($validScopes as $scope) {
            $emission = Emission::factory()->make([
                'production_id' => $this->production->id,
                'emission_factor_id' => $this->emissionFactor->id,
                'scope' => $scope,
            ]);
            $this->assertTrue($emission->save());
        }

        // Invalid scope should fail
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('emissions')->insert([
            'production_id' => $this->production->id,
            'record_date' => '2024-10-23',
            'record_period' => 202410,
            'activity_code' => 'test',
            'scope' => 4, // Invalid scope
            'country' => 'US',
            'emission_factor_id' => $this->emissionFactor->id,
            'calculation_version' => 'v1.0.0',
            'calculated_co2e' => 100.0,
            'record_flags' => 0,
            'data' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_record_period_check_constraint_validation()
    {
        // Valid periods should work
        $validPeriods = [202401, 202412, 999912];
        foreach ($validPeriods as $period) {
            $emission = Emission::factory()->make([
                'production_id' => $this->production->id,
                'emission_factor_id' => $this->emissionFactor->id,
                'record_period' => $period,
            ]);
            $this->assertTrue($emission->save());
        }

        // Invalid period should fail
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('emissions')->insert([
            'production_id' => $this->production->id,
            'record_date' => '2024-10-23',
            'record_period' => 190000, // Invalid period (too early)
            'activity_code' => 'test',
            'scope' => 1,
            'country' => 'US',
            'emission_factor_id' => $this->emissionFactor->id,
            'calculation_version' => 'v1.0.0',
            'calculated_co2e' => 100.0,
            'record_flags' => 0,
            'data' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_country_code_length_check_constraint()
    {
        // Valid 2-letter code should work
        $emission = Emission::factory()->make([
            'production_id' => $this->production->id,
            'emission_factor_id' => $this->emissionFactor->id,
            'country' => 'US',
        ]);
        $this->assertTrue($emission->save());

        // Invalid length should fail (3 letters is invalid now)
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('emissions')->insert([
            'production_id' => $this->production->id,
            'record_date' => '2024-10-23',
            'record_period' => 202410,
            'activity_code' => 'test',
            'scope' => 1,
            'country' => 'USA', // Too long
            'emission_factor_id' => $this->emissionFactor->id,
            'calculation_version' => 'v1.0.0',
            'calculated_co2e' => 100.0,
            'record_flags' => 0,
            'data' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_emission_factor_requirement_check_constraint()
    {
        // Should work with emission_factor_id
        $emission1 = Emission::factory()->make([
            'production_id' => $this->production->id,
            'emission_factor_id' => $this->emissionFactor->id,
            'custom_factor_id' => null,
        ]);
        $this->assertTrue($emission1->save());

        // Should work with custom_factor_id
        $customFactor = CustomEmissionFactor::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $emission2 = Emission::factory()->make([
            'production_id' => $this->production->id,
            'emission_factor_id' => null,
            'custom_factor_id' => $customFactor->id,
        ]);
        $this->assertTrue($emission2->save());

        // Should fail with both null
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('emissions')->insert([
            'production_id' => $this->production->id,
            'record_date' => '2024-10-23',
            'record_period' => 202410,
            'activity_code' => 'test',
            'scope' => 1,
            'country' => 'US',
            'emission_factor_id' => null,
            'custom_factor_id' => null,
            'calculation_version' => 'v1.0.0',
            'calculated_co2e' => 100.0,
            'record_flags' => 0,
            'data' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_foreign_key_constraints_are_enforced()
    {
        // Valid foreign keys should work
        $emission = Emission::factory()->create([
            'production_id' => $this->production->id,
            'emission_factor_id' => $this->emissionFactor->id,
        ]);
        $this->assertNotNull($emission->id);

        // Invalid production_id should fail
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('emissions')->insert([
            'production_id' => 999999, // Non-existent production
            'record_date' => '2024-10-23',
            'record_period' => 202410,
            'activity_code' => 'test',
            'scope' => 1,
            'country' => 'US',
            'emission_factor_id' => $this->emissionFactor->id,
            'calculation_version' => 'v1.0.0',
            'calculated_co2e' => 100.0,
            'record_flags' => 0,
            'data' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_indexes_exist_on_expected_columns()
    {
        // Get all indexes on the emissions table
        $indexes = DB::select("
            SELECT indexname, indexdef 
            FROM pg_indexes 
            WHERE tablename = 'emissions'
        ");

        $indexNames = collect($indexes)->pluck('indexname')->toArray();

        // Check for expected indexes
        $this->assertContains('emissions_production_id_record_date_index', $indexNames);
        $this->assertContains('emissions_data_gin', $indexNames);
        $this->assertContains('emissions_flight_origin_idx', $indexNames);
        $this->assertContains('emissions_flight_destination_idx', $indexNames);
        $this->assertContains('emissions_flight_route_idx', $indexNames);
    }

    public function test_bulk_insert_performance_with_generated_columns()
    {
        $startTime = microtime(true);

        // Insert multiple records to test performance
        $emissions = [];
        for ($i = 0; $i < 100; $i++) {
            $emissions[] = [
                'production_id' => $this->production->id,
                'record_date' => '2024-10-23',
                'record_period' => 202410,
                'activity_code' => 'flight_domestic',
                'scope' => 3,
                'country' => 'US',
                'emission_factor_id' => $this->emissionFactor->id,
                'calculation_version' => 'v1.0.0',
                'calculated_co2e' => rand(100, 1000),
                'record_flags' => 0,
                'data' => json_encode([
                    'flight_origin' => 'SFO',
                    'flight_destination' => 'LAX',
                    'flight_distance_km' => 1850 + rand(-100, 100),
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('emissions')->insert($emissions);

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        // Ensure bulk insert completes reasonably quickly (under 5 seconds)
        $this->assertLessThan(5.0, $duration);

        // Verify all records were inserted with generated columns
        $insertedCount = Emission::whereNotNull('flight_origin')->count();
        $this->assertEquals(100, $insertedCount);
    }
}
