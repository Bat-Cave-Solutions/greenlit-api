<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // No-op: superseded by 2025_10_23_200514_create_emissions_table_v2
        // Keeping this migration file to preserve timestamp ordering without creating the table twice.
        if (Schema::hasTable('emissions')) {
            return;
        }
        // Intentionally do nothing; the v2 migration will create the table with the final schema.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emissions');
    }
};
