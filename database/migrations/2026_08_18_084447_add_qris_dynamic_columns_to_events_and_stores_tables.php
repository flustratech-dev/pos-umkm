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
        Schema::table('stores', function (Blueprint $table) {
            $table->boolean('use_dynamic_qris')->default(false)->after('is_active');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->text('qris_payload')->nullable()->after('qris_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('use_dynamic_qris');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('qris_payload');
        });
    }
};
