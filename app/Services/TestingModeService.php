<?php

namespace App\Services;

use App\Models\Event;
use App\Models\PaymentProof;
use App\Models\RevenueSplit;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TestingModeService
{
    /**
     * Toggle testing mode for an event.
     */
    public function toggleTestingMode(Event $event, ?bool $state = null): bool
    {
        $newState = $state !== null ? $state : !$event->is_testing_mode;
        $event->update(['is_testing_mode' => $newState]);

        return $newState;
    }

    /**
     * Reset and wipe all testing transactions for an event.
     * Keeps stores, owners, products, and event settings 100% intact.
     *
     * @return int Number of deleted transactions
     */
    public function resetTestingTransactions(Event $event): int
    {
        return DB::transaction(function () use ($event) {
            // Find all transactions under stores of this event that are marked as testing
            $transactions = Transaction::whereHas('store', function ($q) use ($event) {
                $q->where('event_id', $event->id);
            })
            ->where('is_testing', true)
            ->with(['paymentProof', 'items', 'revenueSplit'])
            ->get();

            $deletedCount = $transactions->count();

            foreach ($transactions as $tx) {
                // Delete physical proof files
                if ($tx->paymentProof) {
                    if ($tx->paymentProof->proof_path && Storage::disk('public')->exists($tx->paymentProof->proof_path)) {
                        Storage::disk('public')->delete($tx->paymentProof->proof_path);
                    }
                    $tx->paymentProof->delete();
                }

                // Delete revenue splits
                if ($tx->revenueSplit) {
                    $tx->revenueSplit->delete();
                }

                // Delete line items
                $tx->items()->delete();

                // Delete transaction record
                $tx->delete();
            }

            return $deletedCount;
        });
    }
}
