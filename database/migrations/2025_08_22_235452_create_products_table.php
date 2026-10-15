<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {

        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->decimal('cost', 8, 2);
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('min_quantity')->default(0);
            $table->boolean('is_produced')->default(0);

            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnDelete();

            $table->timestamps();

            // 👇 ده اللي بيضيف deleted_at
            $table->softDeletes();
        });
    }



    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
