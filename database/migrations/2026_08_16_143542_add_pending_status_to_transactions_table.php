<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add 'pending' status for cash transactions waiting admin confirmation.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('pending', 'pending_verification', 'paid', 'rejected', 'cancelled') DEFAULT 'pending_verification'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('pending_verification', 'paid', 'rejected', 'cancelled') DEFAULT 'pending_verification'");
        }
    }
};
