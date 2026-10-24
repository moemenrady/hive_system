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
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->text('notes'); // ملاحظة سبب الارتجاع
            $table->string('payment_type', 20)->default('cash'); // طريقة الدفع: كاش أو ديجيتال
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->string('status', 20)->default('pending'); // pending, approved, rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        if (Schema::hasTable('shift_actions') && !Schema::hasColumn('shift_actions', 'refund_request_id')) {
            Schema::table('shift_actions', function (Blueprint $table) {
                $table->foreignId('refund_request_id')->nullable()->constrained('refund_requests')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('shift_actions') && Schema::hasColumn('shift_actions', 'refund_request_id')) {
            Schema::table('shift_actions', function (Blueprint $table) {
                $table->dropForeign(['refund_request_id']);
                $table->dropColumn('refund_request_id');
            });
        }
        Schema::dropIfExists('refund_requests');
    }
};
