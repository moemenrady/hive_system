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

        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('sub_title')->nullable(); // مثلاً: Limited Time
            $table->integer('discount_percentage'); // نسبة الخصم
            $table->string('image')->nullable();
            $table->string('link')->default('#'); // رابط للمنتج أو القسم
            $table->dateTime('expires_at')->nullable(); // لعمل Countdown
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
