<?php

namespace App\Services;

use App\Models\RevenueSplit;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class RevenueSplitService
{
    /**
     * Super Admin flat fee per paid transaction.
     */
    public const SUPERADMIN_FLAT_FEE = 1000.00;

    /**
     * Calculate and persist revenue split for a paid transaction.
     */
    public function calculate(Transaction $transaction): RevenueSplit
    {
        return DB::transaction(function () use ($transaction) {
            $total = (float) $transaction->total_amount;

            $ownerShare = round($total * 0.75, 2);
            $adminGrossShare = round($total * 0.25, 2);
            $superadminShare = self::SUPERADMIN_FLAT_FEE;
            $adminNetShare = round($adminGrossShare - $superadminShare, 2);

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
