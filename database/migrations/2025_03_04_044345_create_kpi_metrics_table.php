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
        Schema::create('kpi_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_category_id')->constrained('kpi_categories')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit');
            $table->decimal('target_value', 10, 2)->default(0);
            $table->decimal('weight', 5, 2)->default(1.0);
            $table->boolean('is_higher_better')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_metrics');
    }
};
