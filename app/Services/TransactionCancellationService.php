<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TransactionCancellationService
{
    /**
     * Cancel a paid transaction with mandatory reason and refund acknowledgment checkbox.
     */
    public function cancel(
        Transaction $transaction,
        User $actor,
        string $reasonCategory,
        ?string $customNote,
        bool $refundAckConfirmed
    ): Transaction {
        if ($transaction->status !== 'paid') {
            throw new InvalidArgumentException("Hanya transaksi berstatus 'paid' yang dapat dibatalkan (Status saat ini: {$transaction->status}).");
        }

        if (!$refundAckConfirmed) {
            throw new InvalidArgumentException('Anda wajib mencentang konfirmasi kesepakatan refund manual sebelum membatalkan transaksi.');
        }

        $fullReason = trim($reasonCategory . ($customNote ? " - {$customNote}" : ''));
        if (empty($fullReason)) {
            throw new InvalidArgumentException('Alasan pembatalan transaksi wajib diisi.');
        }

        return DB::transaction(function () use ($transaction, $actor, $fullReason) {
            $transaction->update([
                'status' => 'cancelled',
                'cancelled_by' => $actor->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $fullReason,
                'refund_ack_confirmed' => true,
            ]);

            return $transaction->load(['items', 'store', 'canceller', 'revenueSplit']);
        });
    }
}
