<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'pgsql') {
            // SQLite/MySQL path not needed for local tests; original column is VARCHAR(3),
            // and changing length in SQLite is not supported without table rebuild.
            return;
        }

        // If already CHAR(2), skip
        $len = DB::table('information_schema.columns')
            ->where('table_schema', 'public')
            ->where('table_name', 'emission_factors')
            ->where('column_name', 'country')
            ->value('character_maximum_length');
        if ((int) $len === 2) {
            return;
        }

        // Convert emission_factors.country to CHAR(2) with uppercase, truncating to first two characters when needed
        DB::statement("ALTER TABLE emission_factors ALTER COLUMN country TYPE CHAR(2) USING UPPER(SUBSTRING(country FROM 1 FOR 2))");

        // Add strict alpha-2 check constraint
        DB::statement("ALTER TABLE emission_factors ADD CONSTRAINT emission_factors_country_alpha2_check CHECK (country ~ '^[A-Z]{2}$')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'pgsql') {
            return;
        }

        // Drop alpha-2 check and widen back to CHAR(3)
        DB::statement("ALTER TABLE emission_factors DROP CONSTRAINT IF EXISTS emission_factors_country_alpha2_check");
        DB::statement("ALTER TABLE emission_factors ALTER COLUMN country TYPE CHAR(3) USING country::text");
    }
};
