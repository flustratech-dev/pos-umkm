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
        Schema::create('revenue_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->unique()->constrained('transactions')->cascadeOnDelete();
            $table->decimal('owner_share', 12, 2);
            $table->decimal('admin_gross_share', 12, 2);
            $table->decimal('superadmin_share', 12, 2)->default(1000);
            $table->decimal('admin_net_share', 12, 2);
            $table->timestamp('calculated_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenue_splits');
    }
};
