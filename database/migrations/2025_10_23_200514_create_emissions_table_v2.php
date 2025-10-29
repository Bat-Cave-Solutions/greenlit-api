<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // If the table already exists (e.g., from a previous partial run), skip creation to be idempotent
        if (Schema::hasTable('emissions')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        Schema::create('emissions', function (Blueprint $table) use ($driver) {
            $table->id();

            // Core relational columns
            $table->unsignedBigInteger('production_id');
            $table->date('record_date');
            $table->integer('record_period'); // YYYYMM format
            $table->unsignedBigInteger('department')->nullable();
            $table->string('activity_code', 50)->index();
            $table->tinyInteger('scope'); // 1, 2, or 3
            $table->string('country', 3); // ISO 3166-1 alpha-3

            // Emission factor references
            $table->unsignedBigInteger('emission_factor_id')->nullable();
            $table->unsignedBigInteger('custom_factor_id')->nullable();

            // Calculation metadata
            $table->string('calculation_version', 20);
            $table->decimal('calculated_co2e', 15, 6)->nullable(); // kg CO2e

            // Record flags (bit flags for audit trail)
            $table->unsignedInteger('record_flags')->default(0);

            // JSON/JSONB column for category-specific inputs
            if ($driver === 'pgsql') {
                // Native JSONB on Postgres
                $table->jsonb('data');
            } else {
                // Fallback JSON type for SQLite and others so tests can run
                $table->json('data');
            }

            // Standard Laravel timestamps
            $table->timestamps();

            // Basic indexes
            $table->index(['production_id', 'record_date']);
            $table->index(['production_id', 'record_period']);
            $table->index(['scope', 'country']);
            $table->index('record_period');
            $table->index('department');
        });

        // Add generated columns for high-usage JSON keys (flight example) — Postgres only
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE emissions ADD COLUMN flight_origin VARCHAR(10) GENERATED ALWAYS AS (data->>'flight_origin') STORED");
            DB::statement("ALTER TABLE emissions ADD COLUMN flight_destination VARCHAR(10) GENERATED ALWAYS AS (data->>'flight_destination') STORED");
            DB::statement("ALTER TABLE emissions ADD COLUMN flight_distance_km DECIMAL(10,2) GENERATED ALWAYS AS (CAST(data->>'flight_distance_km' AS DECIMAL(10,2))) STORED");
        }

        // Create GIN index on JSONB data — Postgres only
        if ($driver === 'pgsql') {
            DB::statement('CREATE INDEX emissions_data_gin ON emissions USING gin (data)');
        }

        // Create indexes on generated columns — Postgres only
        if ($driver === 'pgsql') {
            DB::statement('CREATE INDEX emissions_flight_origin_idx ON emissions (flight_origin)');
            DB::statement('CREATE INDEX emissions_flight_destination_idx ON emissions (flight_destination)');
            DB::statement('CREATE INDEX emissions_flight_route_idx ON emissions (flight_origin, flight_destination)');
        }

        // Add CHECK constraints at database level — Postgres only
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE emissions ADD CONSTRAINT emissions_scope_check CHECK (scope IN (1, 2, 3))');
            DB::statement('ALTER TABLE emissions ADD CONSTRAINT emissions_record_period_check CHECK (record_period >= 190001 AND record_period <= 999912)');
            DB::statement('ALTER TABLE emissions ADD CONSTRAINT emissions_country_check CHECK (LENGTH(country) = 3)');
            DB::statement('ALTER TABLE emissions ADD CONSTRAINT emissions_factor_check CHECK (emission_factor_id IS NOT NULL OR custom_factor_id IS NOT NULL)');
        }

        // CHECK constraints for critical JSON keys based on activity code — Postgres only
        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE emissions ADD CONSTRAINT emissions_flight_data_check CHECK (activity_code NOT LIKE 'flight_%' OR (data ? 'flight_origin' AND data ? 'flight_destination'))");
            DB::statement("ALTER TABLE emissions ADD CONSTRAINT emissions_accommodation_data_check CHECK (activity_code NOT LIKE 'accommodation_%' OR (data ? 'nights' AND data ? 'room_type'))");
            DB::statement("ALTER TABLE emissions ADD CONSTRAINT emissions_waste_data_check CHECK (activity_code NOT LIKE 'waste_%' OR (data ? 'waste_type' AND data ? 'amount'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emissions');
    }
};
