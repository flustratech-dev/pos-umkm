<?php

namespace App\Services;

use App\Models\PaymentProof;
use App\Models\Product;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CheckoutService
{
    public function __construct(
        protected RevenueSplitService $revenueSplitService
    ) {}

    /**
     * Generate unique invoice code: INV-YYYYMMDD-XXXX
     */
    public function generateInvoiceCode(): string
    {
        $dateStr = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        $code = "INV-{$dateStr}-{$random}";

        while (Transaction::where('invoice_code', $code)->exists()) {
            $random = strtoupper(Str::random(4));
            $code = "INV-{$dateStr}-{$random}";
        }

        return $code;
    }

    /**
     * Process cash checkout.
     *
     * @param Store $store
     * @param User $cashier
     * @param array $items Array of ['product_id' => int, 'qty' => int]
     * @param float $amountPaid
     * @return Transaction
     */
    public function processCashCheckout(Store $store, User $cashier, array $items, float $amountPaid): Transaction
    {
        if (empty($items)) {
            throw new InvalidArgumentException('Keranjang belanja tidak boleh kosong.');
        }

        return DB::transaction(function () use ($store, $cashier, $items, $amountPaid) {
            $totalAmount = 0;
            $preparedItems = [];

            foreach ($items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $qty = (int) $itemData['qty'];
                if ($qty <= 0) continue;

                $price = (float) $product->price;
                $subtotal = $price * $qty;
                $totalAmount += $subtotal;

                $preparedItems[] = [
                    'product_id' => $product->id,
                    'title' => $product->title,
                    'price' => $price,
                    'qty' => $qty,
                    'subtotal' => $subtotal,
                ];
            }

            if ($amountPaid < $totalAmount) {
                throw new InvalidArgumentException("Uang tunai diterima (Rp " . number_format($amountPaid, 0, ',', '.') . ") kurang dari total tagihan (Rp " . number_format($totalAmount, 0, ',', '.') . ").");
            }

            $changeDue = $amountPaid - $totalAmount;

            $transaction = Transaction::create([
                'invoice_code' => $this->generateInvoiceCode(),
                'store_id' => $store->id,
                'cashier_id' => $cashier->id,
                'total_amount' => $totalAmount,
                'payment_method' => 'cash',
                'amount_paid' => $amountPaid,
                'change_due' => $changeDue,
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            foreach ($preparedItems as $item) {
                $item['transaction_id'] = $transaction->id;
                TransactionItem::create($item);
            }

            // Immediately calculate revenue split for cash paid
            $this->revenueSplitService->calculate($transaction);

            return $transaction->load(['items', 'store', 'cashier', 'revenueSplit']);
        });
    }

    /**
     * Process QRIS checkout.
     *
     * @param Store $store
     * @param User $cashier
     * @param array $items Array of ['product_id' => int, 'qty' => int]
     * @param UploadedFile|string $proofFile
     * @return Transaction
     */
    public function processQrisCheckout(Store $store, User $cashier, array $items, UploadedFile|string $proofFile): Transaction
    {
        if (empty($items)) {
            throw new InvalidArgumentException('Keranjang belanja tidak boleh kosong.');
        }

        return DB::transaction(function () use ($store, $cashier, $items, $proofFile) {
            $totalAmount = 0;
            $preparedItems = [];

            foreach ($items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $qty = (int) $itemData['qty'];
                if ($qty <= 0) continue;

                $price = (float) $product->price;
                $subtotal = $price * $qty;
                $totalAmount += $subtotal;

                $preparedItems[] = [
                    'product_id' => $product->id,
                    'title' => $product->title,
                    'price' => $price,
                    'qty' => $qty,
                    'subtotal' => $subtotal,
                ];
            }

            $proofPath = '';
            if ($proofFile instanceof UploadedFile) {
                $proofPath = $proofFile->store('payment_proofs', 'public');
            } else {
                $proofPath = (string) $proofFile;
            }

            $transaction = Transaction::create([
                'invoice_code' => $this->generateInvoiceCode(),
                'store_id' => $store->id,
                'cashier_id' => $cashier->id,
                'total_amount' => $totalAmount,
                'payment_method' => 'qris',
                'amount_paid' => $totalAmount,
                'change_due' => 0,
                'status' => 'pending_verification',
            ]);

            foreach ($preparedItems as $item) {
                $item['transaction_id'] = $transaction->id;
                TransactionItem::create($item);
            }

            PaymentProof::create([
                'transaction_id' => $transaction->id,
                'proof_path' => $proofPath,
                'uploaded_at' => now(),
            ]);

            return $transaction->load(['items', 'store', 'cashier', 'paymentProof']);
        });
    }
}
