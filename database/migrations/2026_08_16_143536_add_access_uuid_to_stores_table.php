<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->uuid('access_uuid')->unique()->nullable()->after('booth_number');
        });

        // Generate UUID for existing stores
        \App\Models\Store::whereNull('access_uuid')->each(function ($store) {
            $store->update(['access_uuid' => (string) Str::uuid()]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('access_uuid');
        });
    }
};
