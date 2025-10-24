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
        Schema::create('emission_factors', function (Blueprint $table) {
            $table->id();
            $table->string('activity_code', 50);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('source', 50); // 'climatiq', 'defra', 'epa', etc.
            $table->string('country', 3); // ISO 3166-1 alpha-3
            $table->integer('year');
            $table->string('unit', 20); // 'kg', 'tonne', 'kwh', 'km', etc.
            $table->decimal('co2_factor', 15, 6);
            $table->decimal('ch4_factor', 15, 6)->nullable();
            $table->decimal('n2o_factor', 15, 6)->nullable();
            $table->decimal('co2e_factor', 15, 6); // Combined CO2 equivalent
            $table->jsonb('metadata')->nullable(); // Source-specific metadata
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Unique constraint to prevent duplicates
            $table->unique(['activity_code', 'country', 'year', 'source']);

            // Indexes
            $table->index(['activity_code', 'country']);
            $table->index(['country', 'year']);
            $table->index(['source', 'is_active']);
            $table->index('year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emission_factors');
    }
};
