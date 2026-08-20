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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_negotiable')->default(false)->after('price');
            $table->decimal('min_price', 12, 2)->nullable()->after('is_negotiable');
            $table->decimal('max_price', 12, 2)->nullable()->after('min_price');
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            // Harga acuan (harga pasang) saat transaksi terjadi, untuk struk & laporan nego.
            $table->decimal('original_price', 12, 2)->nullable()->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_negotiable', 'min_price', 'max_price']);
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn('original_price');
        });
    }
};
