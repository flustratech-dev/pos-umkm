<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catatan kenapa transaksi QRIS lunas tanpa bukti transfer, dipakai saat
     * kasir memakai tombol darurat karena upload buktinya gagal.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('proof_failure_reason')->nullable()->after('rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('proof_failure_reason');
        });
    }
};
