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

        Schema::create('employee_transactions', function (Blueprint $table) {
            $table->id();
            // ربط مع جدول المستخدمين
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');

            // معرف المنتج (nullable كما طلبت)
            $table->integer('product_id')->nullable();

            // الكمية (Default 0)
            $table->integer('quantity')->default(0);

            // المبلغ (مهم للسلف والمشتريات)
            $table->decimal('amount', 10, 2)->default(0);

            // نوع العملية: مشتريات أو سلف
            $table->enum('type', ['purchase', 'advance']);

            $table->text('notes')->nullable(); // ملاحظات إضافية
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tmployee_transactions');
    }
};
