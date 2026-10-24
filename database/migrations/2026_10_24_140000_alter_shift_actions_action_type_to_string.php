<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `shift_actions` MODIFY COLUMN `action_type` VARCHAR(50) NOT NULL");
        } else {
            Schema::table('shift_actions', function (Blueprint $table) {
                $table->string('action_type', 50)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op or keep as varchar for backwards compatibility
    }
};
