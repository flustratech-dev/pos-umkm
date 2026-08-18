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
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_testing_mode')->default(false)->after('is_active');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->boolean('is_testing')->default(false)->after('status')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('is_testing_mode');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('is_testing');
        });
    }
};
