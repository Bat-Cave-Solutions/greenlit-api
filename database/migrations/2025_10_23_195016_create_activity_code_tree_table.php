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
        Schema::create('activity_code_tree', function (Blueprint $table) {
            $table->string('code', 50)->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('parent_code', 50)->nullable();
            $table->tinyInteger('level'); // 0=root, 1=category, 2=subcategory, etc.
            $table->tinyInteger('scope'); // 1, 2, or 3
            $table->string('unit', 20)->nullable(); // Expected unit for this activity
            $table->boolean('is_leaf')->default(false); // Can records be assigned directly?
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Foreign key will be added later due to self-referencing constraint

            // Indexes
            $table->index(['parent_code', 'sort_order']);
            $table->index(['scope', 'is_active']);
            $table->index('level');
        });

        // Add CHECK constraint for scope
        DB::statement('ALTER TABLE activity_code_tree ADD CONSTRAINT activity_code_tree_scope_check CHECK (scope IN (1, 2, 3))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_code_tree');
    }
};
