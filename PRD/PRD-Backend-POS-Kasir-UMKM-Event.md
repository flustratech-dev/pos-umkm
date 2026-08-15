# PRD BACKEND — POS Kasir UMKM Event
**Scope:** Fase 2+ — implementasi nyata, hapus semua data dummy, koneksi ke database sungguhan.
**Referensi lengkap business rule:** lihat `PRD-Full-POS-Kasir-UMKM-Event.md`

---

## 1. Migration & Model

Buat migration + Eloquent model + relasi untuk seluruh tabel berikut (urutan disarankan sesuai dependency FK):

1. `events`
2. `users` (tambah kolom `role`, `store_id` nullable — FK ke stores dibuat nullable dulu, di-set setelah tabel stores ada)
3. `stores` (FK `event_id`, `owner_id`)
4. `products` (FK `store_id`, soft delete)
5. `transactions` (FK `store_id`, `cashier_id`, `verified_by` nullable, `cancelled_by` nullable — kolom lengkap lihat bagian 2)
6. `transaction_items` (FK `transaction_id`, `product_id`)
7. `payment_proofs` (FK `transaction_id`, unique)
8. `revenue_splits` (FK `transaction_id`, unique)
9. `helpdesk_tickets` (FK `user_id`)
10. `helpdesk_replies` (FK `ticket_id`, `user_id`)

**Model relations penting:**
```php
// Event
public function stores() { return $this->hasMany(Store::class); }
public function activeStore() ...

// Store
public function event() { return $this->belongsTo(Event::class); }
public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
public function products() { return $this->hasMany(Product::class); }
public function transactions() { return $this->hasMany(Transaction::class); }

// Transaction
public function store() { return $this->belongsTo(Store::class); }
public function items() { return $this->hasMany(TransactionItem::class); }
public function proof() { return $this->hasOne(PaymentProof::class); }
public function revenueSplit() { return $this->hasOne(RevenueSplit::class); }
```

---

## 2. Kolom Wajib Tabel `transactions` (final)
```
id, invoice_code, store_id, cashier_id,
total_amount, payment_method (cash|qris),
amount_paid (nullable), change_due (nullable),      -- khusus cash
status (pending_verification|paid|rejected|cancelled),
paid_at, verified_by, verified_at, rejection_reason, -- khusus qris
cancelled_by, cancelled_at, cancellation_reason,
refund_ack_confirmed (boolean),                       -- hasil checkbox saat cancel
timestamps
```

---

## 3. Service Layer (wajib dipisah dari controller)

### 3.1 `CheckoutService`
- `processCashCheckout(Store $store, array $items, float $amountPaid)`
  - Hitung `total_amount` dari items.
  - Validasi `amountPaid >= total_amount`, kalau tidak lempar exception/validation error.
  - Hitung `change_due = amountPaid - total_amount`.
  - Simpan transaction status `paid` langsung, `paid_at = now()`.
  - Panggil `RevenueSplitService::calculate($transaction)`.
- `processQrisCheckout(Store $store, array $items, UploadedFile $proofImage)`
  - Simpan transaction status `pending_verification`.
  - Simpan `payment_proofs`.
  - **Tidak** memanggil revenue split di sini (baru dihitung saat admin approve).

### 3.2 `RevenueSplitService`
```php
public function calculate(Transaction $transaction): RevenueSplit
{
    $total = $transaction->total_amount;
    $ownerShare = $total * 0.75;
    $adminGross = $total * 0.25;
    $superadminShare = 1000; // flat, final per konfirmasi client
    $adminNet = $adminGross - $superadminShare;

    return RevenueSplit::updateOrCreate(
        ['transaction_id' => $transaction->id],
        [
            'owner_share' => $ownerShare,
            'admin_gross_share' => $adminGross,
            'superadmin_share' => $superadminShare,
            'admin_net_share' => $adminNet,
            'calculated_at' => now(),
        ]
    );
}
```
> Dipanggil di 2 tempat: (1) saat cash langsung paid, (2) saat admin approve QRIS. **Tidak dipanggil ulang** saat status berubah jadi `cancelled` — data revenue split lama tetap ada untuk audit, tapi query laporan **wajib exclude status `cancelled`** dari total revenue (lihat 3.4).

### 3.3 `TransactionVerificationService` (khusus QRIS)
- `approve(Transaction $transaction, User $admin)`: set status `paid`, `verified_by`, `verified_at`, `paid_at`, lalu panggil `RevenueSplitService::calculate()`.
- `reject(Transaction $transaction, User $admin, string $reason)`: set status `rejected`, simpan `rejection_reason`. Tidak ada revenue split untuk transaksi rejected.

### 3.4 `TransactionCancellationService` (BARU)
- `cancel(Transaction $transaction, User $admin, string $reasonCategory, ?string $note, bool $refundAckConfirmed)`
  - Validasi: hanya bisa cancel transaksi dengan status `paid`.
  - Validasi: `$refundAckConfirmed` harus `true` — kalau `false`, tolak dengan pesan "Checkbox konfirmasi refund wajib dicentang".
  - Set status `cancelled`, simpan `cancelled_by`, `cancelled_at`, `cancellation_reason` (gabungan kategori + note), `refund_ack_confirmed = true`.
  - **Tidak menghapus** `revenue_splits` — data historis tetap ada, tapi ditandai lewat status transaksi induk.

### 3.5 `EventService` (BARU)
- `createEvent(array $data, User $superadmin)`: buat event baru dengan `is_active = false` default.
- `activateEvent(Event $event)`:
  - Set semua event lain `is_active = false`.
  - Set event ini `is_active = true`.
  - Wrap dalam DB transaction supaya atomic (tidak boleh 2 event aktif bersamaan).

---

## 4. Query Laporan — Aturan Penting

Semua query total revenue (di laporan User/Admin/Superadmin) **WAJIB**:
```php
Transaction::where('status', 'paid')  // exclude pending_verification, rejected, cancelled
    ->whereHas('store', fn($q) => $q->where('event_id', $currentEventId))
    ...
```
- Transaksi `cancelled` tetap **ditampilkan di tabel/list laporan** (untuk transparansi audit), tapi **dikecualikan dari kalkulasi total revenue**.
- Scoping event: Admin & User hanya query data event aktif. Super Admin bisa pilih event via parameter `event_id` di request (default: event aktif).

---

## 5. Middleware & Otorisasi

```php
// routes/web.php (contoh grouping)
Route::middleware(['auth', 'role:user'])->group(function () { ... });
Route::middleware(['auth', 'role:admin'])->group(function () { ... });
Route::middleware(['auth', 'role:superadmin'])->group(function () { ... });
```
- Buat custom middleware `EnsureRole` yang cek `auth()->user()->role`.
- Tambahan khusus: middleware/policy untuk memastikan **User hanya bisa akses/edit data milik `store_id` sendiri** (mis. `ProductPolicy`, `TransactionPolicy`).
- **Cancel transaksi** hanya boleh dipanggil role `admin`/`superadmin` — cek eksplisit di `TransactionPolicy::cancel()`.

---

## 6. Validasi (FormRequest)

| FormRequest | Aturan Kunci |
|---|---|
| `RegisterRequest` | email unique, username unique, phone required, password min 8 + confirmed |
| `ProductRequest` | title required, price numeric min 0, photo image max 2MB |
| `CashCheckoutRequest` | items required array min 1, amount_paid numeric, **custom rule: amount_paid >= total dihitung dari items** |
| `QrisCheckoutRequest` | items required array min 1, proof_image required image max 2MB |
| `CancelTransactionRequest` | reason_category required in:list, note required_if:reason_category,lainnya, **refund_ack_confirmed required accepted** |
| `CreateEventRequest` | name required, start_date/end_date nullable date, end_date after_or_equal:start_date |

---

## 7. Seeder

`DatabaseSeeder.php` memanggil `UserSeeder` (superadmin + admin dari `.env`, lihat contoh di PRD Full bagian 9). **Tidak ada seeder untuk `events`, `stores`, `products`, `transactions`** — semua kosong sampai ada input real melalui: (a) Super Admin create event pertama, (b) User register & buat warung.

---

## 8. Storage & File

- `php artisan storage:link`.
- Folder terpisah: `storage/app/public/products/`, `storage/app/public/payment_proofs/`.
- Validasi tipe file (jpg/png) & ukuran maks di FormRequest, jangan hanya divalidasi di frontend.

---

## 9. PDF Laporan

- Pakai `barryvdh/laravel-dompdf` (atau `spatie/laravel-pdf`).
- Blade view laporan terpisah per role (`reports.user-pdf`, `reports.admin-pdf`, `reports.superadmin-pdf`) supaya scope data & kolom bisa beda (mis. kolom uang diterima/kembalian hanya relevan di laporan user & admin, kolom breakdown 3 pihak hanya di laporan admin/superadmin).

---

## 10. Checklist Selesai Fase Backend
- [ ] Semua migration & model + relasi jalan, `php artisan migrate` bersih
- [ ] `UserSeeder` jalan dari `.env`, login superadmin & admin berhasil
- [ ] Semua data dummy di view **sudah dihapus**, diganti query Eloquent real
- [ ] `CheckoutService` (cash dengan kembalian, QRIS dengan bukti) jalan end-to-end
- [ ] `RevenueSplitService` teruji dengan unit test (minimal: transaksi normal, transaksi kecil di bawah Rp4.000)
- [ ] `TransactionVerificationService` (approve/reject QRIS) jalan
- [ ] `TransactionCancellationService` — cancel hanya bisa oleh admin/superadmin, checkbox validasi bekerja, revenue exclude dari laporan
- [ ] `EventService` — create & activate event, hanya 1 event aktif dalam satu waktu (teruji atomic)
- [ ] Policy/middleware role & ownership teruji (user tidak bisa akses data warung lain)
- [ ] Laporan PDF jalan per role dengan scope data yang benar
