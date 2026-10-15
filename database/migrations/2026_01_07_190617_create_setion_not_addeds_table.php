<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('setion_not_addeds', function (Blueprint $table) {
      $table->id();
      $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
      $table->integer('persons')->default(1);
      $table->timestamp('start_time')->useCurrent();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('setion_not_addeds');
  }
};
