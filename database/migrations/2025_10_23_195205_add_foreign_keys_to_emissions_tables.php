<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // Skip on non-Postgres drivers; SQLite cannot safely add FKs post-creation.
        if ($driver !== 'pgsql') {
            return;
        }

        // Productions and custom_emission_factors FKs are now defined inline in their create migrations.

        // Emissions foreign keys are defined inline in the creation migration (v2).
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

        // Emissions foreign keys are dropped with the table in its own migration.

        Schema::table('custom_emission_factors', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
        });

        Schema::table('productions', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
        });
    }
};
