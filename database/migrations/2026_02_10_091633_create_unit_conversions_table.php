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
       Schema::create('unit_conversions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('from_unit_id')->constrained('units');
    $table->foreignId('to_unit_id')->constrained('units');
    $table->decimal('factor', 10, 4);
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_conversions');
    }
};
