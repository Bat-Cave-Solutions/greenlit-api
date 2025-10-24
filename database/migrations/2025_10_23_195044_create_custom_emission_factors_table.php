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
        Schema::create('custom_emission_factors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->string('activity_code', 50);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit', 20);
            $table->decimal('co2_factor', 15, 6);
            $table->decimal('ch4_factor', 15, 6)->nullable();
            $table->decimal('n2o_factor', 15, 6)->nullable();
            $table->decimal('co2e_factor', 15, 6);
            $table->jsonb('metadata')->nullable(); // User-provided metadata
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['organization_id', 'activity_code']);
            $table->index(['organization_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_emission_factors');
    }
};
