<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TransactionVerificationService
{
    public function __construct(
        protected RevenueSplitService $revenueSplitService
    ) {}

    /**
     * Approve pending QRIS transaction.
     */
    public function approve(Transaction $transaction, User $verifier): Transaction
    {
        if ($transaction->status !== 'pending_verification') {
            throw new InvalidArgumentException("Transaksi tidak dalam status pending verification (Status saat ini: {$transaction->status}).");
        }

        return DB::transaction(function () use ($transaction, $verifier) {
            $transaction->update([
                'status' => 'paid',
                'paid_at' => now(),
                'verified_by' => $verifier->id,
                'verified_at' => now(),
                'rejection_reason' => null,
            ]);

            // Calculate revenue split upon approval
            $this->revenueSplitService->calculate($transaction);

            return $transaction->load(['items', 'store', 'verifier', 'revenueSplit']);
        });
    }

    /**
     * Reject pending transaction (QRIS or Cash).
     */
    public function reject(Transaction $transaction, User $verifier, string $reason): Transaction
    {
        if (!in_array($transaction->status, ['pending', 'pending_verification'])) {
            throw new InvalidArgumentException("Transaksi tidak dalam status pending (Status saat ini: {$transaction->status}).");
        }

        if (empty(trim($reason))) {
            throw new InvalidArgumentException('Alasan penolakan wajib diisi.');
        }

        return DB::transaction(function () use ($transaction, $verifier, $reason) {
            $transaction->update([
                'status' => 'rejected',
                'verified_by' => $verifier->id,
                'verified_at' => now(),
                'rejection_reason' => $reason,
            ]);

            return $transaction->load(['items', 'store', 'verifier']);
        });
    }

    /**
     * Confirm pending cash transaction (admin received cash at exit booth).
     */
    public function confirmCash(Transaction $transaction, User $verifier): Transaction
    {
        if ($transaction->status !== 'pending') {
            throw new InvalidArgumentException("Transaksi tidak dalam status pending (Status saat ini: {$transaction->status}).");
        }

        if ($transaction->payment_method !== 'cash') {
            throw new InvalidArgumentException("Transaksi ini bukan transaksi cash.");
        }

        return DB::transaction(function () use ($transaction, $verifier) {
            $transaction->update([
                'status' => 'paid',
                'paid_at' => now(),
                'verified_by' => $verifier->id,
                'verified_at' => now(),
            ]);

            // Calculate revenue split upon cash confirmation
            $this->revenueSplitService->calculate($transaction);

            return $transaction->load(['items', 'store', 'verifier', 'revenueSplit']);
        });
    }
}
