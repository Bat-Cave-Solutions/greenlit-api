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
        Schema::table('productions', function (Blueprint $table) {
            $table->foreign('organization_id')->references('id')->on('organizations');
        });

        Schema::table('custom_emission_factors', function (Blueprint $table) {
            $table->foreign('organization_id')->references('id')->on('organizations');
        });

        Schema::table('emissions', function (Blueprint $table) {
            $table->foreign('production_id')->references('id')->on('productions');
            $table->foreign('emission_factor_id')->references('id')->on('emission_factors');
            $table->foreign('custom_factor_id')->references('id')->on('custom_emission_factors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emissions', function (Blueprint $table) {
            $table->dropForeign(['production_id']);
            $table->dropForeign(['emission_factor_id']);
            $table->dropForeign(['custom_factor_id']);
        });

        Schema::table('custom_emission_factors', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
        });

        Schema::table('productions', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
        });
    }
};
