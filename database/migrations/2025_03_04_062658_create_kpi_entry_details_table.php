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
        Schema::create('kpi_entry_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entry_id')->constrained('kpi_daily_entries')->onDelete('cascade');
            $table->foreignId('metric_id')->constrained('kpi_metrics')->onDelete('cascade');
            $table->decimal('value', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_entry_details');
    }
};
