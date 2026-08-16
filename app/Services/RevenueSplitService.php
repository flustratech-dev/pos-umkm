<?php

namespace App\Services;

use App\Models\RevenueSplit;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class RevenueSplitService
{
    /**
     * Revenue split percentages.
     */
    public const OWNER_PERCENTAGE = 0.75;       // 75% for Warung/Tenant
    public const ADMIN_PERCENTAGE = 0.225;       // 22.5% for Admin/EO
    public const SUPERADMIN_PERCENTAGE = 0.025;  // 2.5% for Developer/Superadmin

    /**
     * Calculate and persist revenue split for a paid transaction.
     */
    public function calculate(Transaction $transaction): RevenueSplit
    {
        return DB::transaction(function () use ($transaction) {
            $total = (float) $transaction->total_amount;

            $ownerShare = round($total * self::OWNER_PERCENTAGE, 2);
            $adminGrossShare = round($total * self::ADMIN_PERCENTAGE, 2);
            $superadminShare = round($total * self::SUPERADMIN_PERCENTAGE, 2);
            $adminNetShare = $adminGrossShare; // Admin net = admin gross (dev fee is separate)

            return RevenueSplit::updateOrCreate(
                ['transaction_id' => $transaction->id],
                [
                    'owner_share' => $ownerShare,
                    'admin_gross_share' => $adminGrossShare,
                    'superadmin_share' => $superadminShare,
                    'admin_net_share' => $adminNetShare,
                    'calculated_at' => now(),
                ]
            );
        });
    }
}
