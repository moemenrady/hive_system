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
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // Espresso Beans, Vanilla Syrup
            $table->decimal('stock', 10, 2);  // الكمية الحالية

            // 👇 الحد الأدنى للتنبيه
            $table->decimal('alert_stock', 10, 2)->default(0);

            $table->foreignId('unit_id')->constrained('units');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
