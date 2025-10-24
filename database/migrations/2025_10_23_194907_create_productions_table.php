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
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('calculation_version', 20);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('organization_id');
            $table->date('base_year');
            $table->date('reporting_period_start');
            $table->date('reporting_period_end');
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
            $table->index('reporting_period_start');
            $table->index('reporting_period_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productions');
    }
};
