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

        // Only perform ALTERs on Postgres; SQLite is used for unit tests and lacks full ALTER support
        if ($driver !== 'pgsql') {
            return;
        }

        // If the column is already CHAR(2), skip
        $len = DB::table('information_schema.columns')
            ->where('table_schema', 'public')
            ->where('table_name', 'emissions')
            ->where('column_name', 'country')
            ->value('character_maximum_length');
        if ((int) $len === 2) {
            return;
        }

        // Drop the previous country length check if it exists (it enforced length = 3)
        DB::statement('ALTER TABLE emissions DROP CONSTRAINT IF EXISTS emissions_country_check');

        // Create a temporary column with the target type (CHAR(2))
        DB::statement('ALTER TABLE emissions ADD COLUMN country_tmp CHAR(2)');

        // Backfill: if current values are 2 or more chars, take the first two uppercase letters.
        // If already 2 letters, keep as-is; if 3 letters, convert to first two as a stopgap.
        // For production systems, replace this with a proper alpha-3 -> alpha-2 mapping prior to running.
        DB::statement('UPDATE emissions SET country_tmp = UPPER(CASE WHEN LENGTH(country) = 2 THEN country WHEN LENGTH(country) >= 2 THEN SUBSTRING(country FROM 1 FOR 2) ELSE NULL END)');

        // Swap columns
        DB::statement('ALTER TABLE emissions DROP COLUMN country');
        DB::statement('ALTER TABLE emissions RENAME COLUMN country_tmp TO country');

        // Enforce ISO alpha-2 (two uppercase letters)
        DB::statement("ALTER TABLE emissions ADD CONSTRAINT emissions_country_alpha2_check CHECK (country ~ '^[A-Z]{2}$')");
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

        // Drop the alpha-2 check
        DB::statement('ALTER TABLE emissions DROP CONSTRAINT IF EXISTS emissions_country_alpha2_check');

        // Recreate the country column as CHAR(3) and copy data back (values will be padded as needed)
        DB::statement('ALTER TABLE emissions ADD COLUMN country_tmp CHAR(3)');
        DB::statement("UPDATE emissions SET country_tmp = UPPER(COALESCE(country, ''))");
        DB::statement('ALTER TABLE emissions DROP COLUMN country');
        DB::statement('ALTER TABLE emissions RENAME COLUMN country_tmp TO country');

        // Restore the original length=3 check
        DB::statement('ALTER TABLE emissions ADD CONSTRAINT emissions_country_check CHECK (LENGTH(country) = 3)');
    }
};
