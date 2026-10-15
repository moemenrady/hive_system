<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('shift_actions', function (Blueprint $table) {
            $table->string('payment_type')->nullable()->after('expense_amount');
        });
    }

    public function down()
    {
        Schema::table('shift_actions', function (Blueprint $table) {
            $table->dropColumn('payment_type');
        });
    }
};
