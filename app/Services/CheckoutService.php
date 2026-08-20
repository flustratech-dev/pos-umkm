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
     * Harga yang dipakai untuk satu baris keranjang.
     *
     * Produk harga pas selalu memakai harga dari database, input kasir diabaikan.
     * Produk tawar-menawar boleh memakai harga hasil nego selama masih berada di
     * dalam rentang yang ditetapkan pemilik stand.
     */
    protected function resolveItemPrice(Product $product, array $itemData): float
    {
        if (!$product->is_negotiable || !array_key_exists('price', $itemData) || $itemData['price'] === null || $itemData['price'] === '') {
            return $product->is_negotiable ? (float) $product->listPrice() : (float) $product->price;
        }

        $price = (float) $itemData['price'];
        [$min, $max] = $product->priceRange();

        if (!$product->acceptsPrice($price)) {
            throw new InvalidArgumentException(
                "Harga nego untuk '{$product->title}' (Rp " . number_format($price, 0, ',', '.') . ") "
                . "di luar rentang yang diizinkan: Rp " . number_format($min, 0, ',', '.')
                . " - Rp " . number_format($max, 0, ',', '.') . "."
            );
        }

        return $price;
    }

    /**
     * Process cash checkout.
     * Cash transactions now go to 'pending' status until admin confirms payment.
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

                $price = $this->resolveItemPrice($product, $itemData);
                $subtotal = $price * $qty;
                $totalAmount += $subtotal;

                $preparedItems[] = [
                    'product_id' => $product->id,
                    'title' => $product->title,
                    'price' => $price,
                    'original_price' => $product->listPrice(),
                    'qty' => $qty,
                    'subtotal' => $subtotal,
                ];
            }

            if ($amountPaid < $totalAmount) {
                throw new InvalidArgumentException("Uang tunai diterima (Rp " . number_format($amountPaid, 0, ',', '.') . ") kurang dari total tagihan (Rp " . number_format($totalAmount, 0, ',', '.') . ").");
            }

            $changeDue = $amountPaid - $totalAmount;
            $isTesting = (bool) ($store->event?->is_testing_mode);

            // Cash transactions are now 'pending' until admin confirms at exit cashier
            $transaction = Transaction::create([
                'invoice_code' => $this->generateInvoiceCode(),
                'store_id' => $store->id,
                'cashier_id' => $cashier->id,
                'total_amount' => $totalAmount,
                'payment_method' => 'cash',
                'amount_paid' => $amountPaid,
                'change_due' => $changeDue,
                'status' => 'pending',
                'is_testing' => $isTesting,
                'paid_at' => null,
            ]);

            foreach ($preparedItems as $item) {
                $item['transaction_id'] = $transaction->id;
                TransactionItem::create($item);
            }

            // Revenue split is calculated AFTER admin confirms payment, not at checkout
            // See TransactionVerificationService::confirmCash()

            return $transaction->load(['items', 'store', 'cashier']);
        });
    }

    /**
     * Process a QRIS transaction (langsung lunas, disertai arsip bukti transfer).
     *
     * @param Store $store
     * @param User $cashier
     * @param array $items Array of ['product_id' => int, 'qty' => int]
     * @param UploadedFile $proofFile Bukti transfer, wajib sebagai arsip verifikasi EO
     * @return Transaction
     */
    public function processQrisCheckout(Store $store, User $cashier, array $items, UploadedFile $proofFile): Transaction
    {
        return $this->recordQrisTransaction($store, $cashier, $items, $proofFile, null);
    }

    /**
     * Catat transaksi QRIS yang uangnya sudah masuk ke rekening tapi bukti
     * transfernya gagal diunggah (mis. file ditolak server).
     *
     * Statusnya tetap lunas seperti QRIS biasa — tidak perlu persetujuan admin,
     * karena pembayarannya memang sudah diterima. Alasannya disimpan supaya EO
     * tahu kenapa transaksi ini tidak punya arsip bukti.
     */
    public function processQrisCheckoutWithoutProof(Store $store, User $cashier, array $items, string $reason): Transaction
    {
        if (trim($reason) === '') {
            throw new InvalidArgumentException('Alasan bukti tidak terunggah wajib diisi.');
        }

        return $this->recordQrisTransaction($store, $cashier, $items, null, trim($reason));
    }

    protected function recordQrisTransaction(
        Store $store,
        User $cashier,
        array $items,
        ?UploadedFile $proofFile,
        ?string $proofFailureReason
    ): Transaction {
        if (empty($items)) {
            throw new InvalidArgumentException('Keranjang belanja tidak boleh kosong.');
        }

        return DB::transaction(function () use ($store, $cashier, $items, $proofFile, $proofFailureReason) {
            $totalAmount = 0;
            $preparedItems = [];

            foreach ($items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $qty = (int) $itemData['qty'];
                if ($qty <= 0) continue;

                $price = $this->resolveItemPrice($product, $itemData);
                $subtotal = $price * $qty;
                $totalAmount += $subtotal;

                $preparedItems[] = [
                    'product_id' => $product->id,
                    'title' => $product->title,
                    'price' => $price,
                    'original_price' => $product->listPrice(),
                    'qty' => $qty,
                    'subtotal' => $subtotal,
                ];
            }

            $uniqueCode = $store->unique_code;
            $totalAmount += $uniqueCode;
            $isTesting = (bool) ($store->event?->is_testing_mode);

            $transaction = Transaction::create([
                'invoice_code' => $this->generateInvoiceCode(),
                'store_id' => $store->id,
                'cashier_id' => $cashier->id,
                'total_amount' => $totalAmount,
                'payment_method' => 'qris',
                'amount_paid' => $totalAmount,
                'change_due' => 0,
                'status' => 'paid',
                'is_testing' => $isTesting,
                'paid_at' => now(),
                'proof_failure_reason' => $proofFailureReason,
            ]);

            foreach ($preparedItems as $item) {
                $item['transaction_id'] = $transaction->id;
                TransactionItem::create($item);
            }

            if ($proofFile) {
                PaymentProof::create([
                    'transaction_id' => $transaction->id,
                    'proof_path' => $proofFile->store('payment_proofs', 'public'),
                ]);
            }

            // Generate revenue split immediately since it's auto-success
            $this->revenueSplitService->calculate($transaction);

            return $transaction->load(['items', 'store', 'cashier', 'paymentProof', 'revenueSplit']);
        });
    }
}
