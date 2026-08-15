# PRD FULL — Web App POS Kasir UMKM Event
**Versi:** 2.0 (update setelah konfirmasi client)
**Tanggal:** 15 Agustus 2026

---

## 1. Ringkasan Proyek

EO membangun event UMKM berkala (multi-event ke depannya). Sistem POS kasir dipakai langsung di lokasi event oleh pemilik warung, mendukung cash & QRIS (QRIS statis, 1 akun milik EO untuk semua warung), dengan bagi hasil otomatis per transaksi dan verifikasi manual bukti QRIS.

**Konfirmasi kunci dari client:**
| # | Keputusan |
|---|---|
| 1 | Potongan Super Admin **tetap flat Rp1.000/transaksi**, diambil dari bagian Admin (25%), tanpa pengecualian nominal kecil |
| 2 | Registrasi user **langsung aktif**, tanpa approval — verifikasi peserta sudah dilakukan di luar sistem (offline) |
| 3 | QRIS **statis** untuk versi awal (1 gambar QRIS EO untuk semua warung) |
| 4 | **Cancel/refund transaksi setelah `paid` diizinkan**, dengan alasan wajib + checkbox konfirmasi |
| 5 | Sistem **multi-event** — tiap event baru mulai dengan data warung/produk/transaksi kosong (0), data event lama tetap tersimpan sebagai arsip |
| 6 | **Hanya 1 akun admin** per instalasi (tidak perlu manajemen banyak admin) |

---

## 2. Aktor & Role

| Role | Deskripsi |
|---|---|
| **Super Admin** | Developer/kamu — kelola event, full visibility sistem |
| **Admin (EO)** | 1 akun tetap — kelola operasional 1 event aktif |
| **User (Pemilik Warung)** | Registrasi mandiri, langsung aktif, terikat ke event aktif saat mendaftar |

### Matriks Hak Akses
| Fitur | User | Admin | Super Admin |
|---|:---:|:---:|:---:|
| CRUD produk milik sendiri | ✅ | ❌ | ❌ |
| Lihat semua produk/warung | ❌ | ✅ | ✅ |
| Checkout/kasir | ✅ | ❌ | ❌ |
| Verifikasi bukti QRIS | ❌ | ✅ | 👁️ lihat saja |
| Cancel transaksi paid | ❌ | ✅ | ✅ |
| Laporan | ✅ (sendiri) | ✅ (1 event aktif) | ✅ (semua event) |
| Kelola event (create/switch) | ❌ | ❌ | ✅ |
| Helpdesk | buat & lihat sendiri | lihat & balas semua | lihat semua |

---

## 3. Aturan Bisnis

### 3.1 Bagi Hasil (tetap sama, dikonfirmasi final)
```
Total Transaksi (T) — hanya dihitung saat status = paid
├── Pemilik Warung   = 75% × T
├── Admin (gross)    = 25% × T
└── Super Admin      = Rp1.000 flat, dipotong dari Admin
    Admin (net)      = Admin gross − Rp1.000
```
> Disepakati: berlaku sama untuk semua nominal transaksi, termasuk yang sangat kecil (bagian admin net bisa jadi minus secara matematis pada transaksi < ~Rp4.000, tapi ini sudah diterima client sebagai risiko yang disadari, bukan bug).

### 3.2 Alur Pembayaran Cash (BARU — detail kembalian)
1. Kasir pilih metode **Cash**.
2. Sistem tampilkan input **"Uang Diterima"** (amount_paid).
3. Sistem otomatis hitung **Kembalian** = `amount_paid − total_amount`, tampil real-time saat kasir mengetik nominal.
4. Validasi: `amount_paid` tidak boleh kurang dari `total_amount` (tombol konfirmasi disabled kalau kurang, dengan warning "Uang diterima kurang").
5. Setelah konfirmasi → transaksi langsung `paid`, `amount_paid` dan `change_due` disimpan permanen di transaksi.
6. **Laporan** menampilkan kolom Uang Diterima & Kembalian khusus untuk transaksi metode cash (kolom kosong/"-" untuk QRIS).

### 3.3 Alur Pembayaran QRIS (tetap seperti PRD v1)
Tampilkan QRIS statis → upload bukti → status `pending_verification` → alert "menunggu verifikasi, belum berhasil" → Admin approve/reject → jadi `paid`/`rejected` → revenue split dihitung saat `paid`.

### 3.4 Alur Cancel/Refund Setelah Paid (BARU)
Karena uang QRIS terkumpul di 1 akun EO dan cash sudah diterima langsung oleh warung, pembatalan transaksi **tidak otomatis mengembalikan uang** — sistem hanya mencatat status. Refund fisik/uang tetap dilakukan manual di luar sistem oleh admin/warung.

**Siapa yang bisa cancel:** Admin & Super Admin saja (bukan User/pemilik warung, untuk mencegah warung membatalkan transaksi sepihak tanpa sepengetahuan EO).

**Form Cancel Transaksi** (muncul saat admin klik "Batalkan" pada transaksi berstatus `paid`):
- Dropdown/alasan pembatalan (pilihan cepat): `Salah input barang/harga`, `Barang dikembalikan customer`, `Kesalahan sistem`, `Lainnya (isi manual)`.
- Textarea catatan tambahan (wajib jika pilih "Lainnya").
- **Checkbox wajib dicentang**: *"Saya konfirmasi bahwa pembatalan ini sudah dikoordinasikan dengan pemilik warung dan/atau refund ke customer (jika ada) sudah/akan ditangani secara manual di luar sistem."*
  - Alasan checkbox ini ada: karena sistem **tidak** memproses refund uang secara otomatis (baik cash maupun QRIS), checkbox ini adalah bentuk konfirmasi eksplisit dari admin bahwa proses refund manual sudah disadari & jadi tanggung jawabnya — supaya tidak ada pembatalan asal klik tanpa koordinasi ke warung terkait.
- Tombol "Batalkan Transaksi" **disabled sampai checkbox dicentang**.

**Efek setelah cancel:**
- Status transaksi → `cancelled`.
- `revenue_splits` transaksi tsb **tidak dihapus** (untuk audit trail) tapi **dikecualikan dari total revenue** di semua laporan (owner, admin, superadmin).
- Tercatat `cancelled_by`, `cancelled_at`, `cancellation_reason` di tabel transaksi.
- Transaksi `cancelled` tetap muncul di laporan dengan badge status berbeda (bukan dihapus dari histori), supaya tetap bisa diaudit.

### 3.5 Multi-Event (BARU)
- Super Admin bisa **Create Event Baru** dari panel Super Admin: isi nama event, tanggal mulai/selesai.
- Hanya **1 event yang berstatus `is_active = true`** pada satu waktu (event aktif = event yang sedang berjalan dan dipakai untuk registrasi & kasir).
- Saat event baru dibuat & diaktifkan:
  - Event sebelumnya otomatis jadi `is_active = false` (arsip, read-only, tetap bisa diakses lewat laporan Super Admin).
  - **Registrasi warung baru hanya bisa masuk ke event yang sedang aktif.**
  - Data warung/produk/transaksi event lama **tidak dihapus**, hanya tidak muncul lagi di tampilan operasional Admin/User untuk event baru — semua mulai dari 0 secara tampilan.
- Admin & User yang login hanya melihat data event aktif (kecuali Super Admin yang bisa switch/lihat semua event via laporan histori).
- Pemilik warung yang ikut event baru **wajib daftar ulang** (akun terikat ke 1 event), untuk menjaga kesederhanaan sistem sesuai sifat "sekali pakai per event".

---

## 4. Fitur Detail per Role

### 4.1 User (Pemilik Warung)
1. Tambah produk (foto, judul, harga)
2. Detail produk (CRUD & delete, soft delete)
3. Kasir/checkout — cash (dengan input uang diterima & kembalian) / QRIS (upload bukti)
4. Laporan — riwayat transaksi milik sendiri (termasuk kolom kembalian utk cash, status termasuk cancelled), download PDF, total revenue (net 75%)
5. Helpdesk
6. User guide

### 4.2 Admin (EO)
1. Dashboard (ringkasan event aktif: total transaksi, revenue, warung aktif, antrian verifikasi QRIS)
2. Daftar produk semua warung (read-only)
3. Daftar warung & pemilik
4. Laporan full — semua warung di event aktif, breakdown revenue, bukti bayar QRIS, filter status termasuk `cancelled`
5. Verifikasi pembayaran QRIS (approve/reject)
6. **Cancel transaksi paid** (form di 3.4)
7. Helpdesk (kelola semua tiket)
8. User guide

### 4.3 Super Admin
1. Full visibility semua event (aktif & arsip)
2. **Kelola Event** — create event baru, lihat daftar semua event, switch tampilan laporan antar event
3. Laporan — termasuk revenue Super Admin (akumulasi Rp1.000 × transaksi paid), per event maupun gabungan semua event

---

## 5. Skema Database (Update)

### `events` (BARU)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| name | string | mis. "Bazar UMKM Agustus 2026" |
| slug | string, unique | |
| start_date | date, nullable | |
| end_date | date, nullable | |
| is_active | boolean, default false | hanya 1 event boleh true |
| created_by | FK users.id | superadmin pembuat |
| timestamps | |

### `stores` (update: tambah event_id)
| Kolom | Tipe |
|---|---|
| id | bigint PK |
| event_id | FK → events.id |
| owner_id | FK → users.id |
| name | string |
| is_active | boolean |
| timestamps | |

### `users` — tidak berubah dari v1 (role: superadmin/admin/user, store_id nullable)

### `products` — tidak berubah dari v1 (store_id, title, photo, price, soft delete)

### `transactions` (update besar)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| invoice_code | string, unique | |
| store_id | FK | |
| cashier_id | FK users | |
| total_amount | decimal(12,2) | |
| payment_method | enum('cash','qris') | |
| **amount_paid** | decimal(12,2), nullable | **BARU** — uang diterima (khusus cash) |
| **change_due** | decimal(12,2), nullable | **BARU** — kembalian (khusus cash) |
| status | enum('pending_verification','paid','rejected','**cancelled**') | tambah status cancelled |
| paid_at | timestamp, nullable | |
| verified_by / verified_at | FK / timestamp, nullable | khusus QRIS |
| rejection_reason | string, nullable | khusus QRIS reject |
| **cancelled_by** | FK users, nullable | **BARU** |
| **cancelled_at** | timestamp, nullable | **BARU** |
| **cancellation_reason** | text, nullable | **BARU** |
| **refund_ack_confirmed** | boolean, default false | **BARU** — hasil centang checkbox saat cancel |
| timestamps | |

### `transaction_items`, `payment_proofs`, `revenue_splits`, `helpdesk_tickets`, `helpdesk_replies`
— tetap sama seperti PRD v1, tidak ada perubahan struktur.

> Catatan implementasi: `event_id` tidak perlu diulang di setiap tabel — cukup diturunkan dari `stores.event_id` lewat relasi (`transaction → store → event`), supaya tidak ada duplikasi & data tetap konsisten.

---

## 6. Route/Endpoint (Update dari v1)

Tambahan dari daftar route PRD v1:
```
Admin:
POST   /admin/transaksi/{id}/cancel     -> submit form cancel (alasan + checkbox)

Super Admin:
GET    /superadmin/event                -> daftar semua event
POST   /superadmin/event                -> create event baru
POST   /superadmin/event/{id}/activate  -> set event ini jadi aktif, nonaktifkan yang lain
GET    /superadmin/laporan?event_id=    -> filter laporan per event
```
Route checkout user juga diperbarui:
```
POST /kasir/checkout  -> body sekarang menerima payment_method, dan jika cash: amount_paid (server hitung change_due)
```

---

## 7. Fase Pengembangan (update)

| Fase | Scope |
|---|---|
| 0 | Auth, role, seeder, **model Event + activate logic** |
| 1 | Frontend full dummy (semua halaman termasuk cancel form & event switcher) |
| 2 | Backend real: migration, model, CRUD produk |
| 3 | Kasir: checkout cash (uang diterima/kembalian) + QRIS + verifikasi |
| 4 | **Cancel/refund flow** + laporan exclude cancelled dari revenue |
| 5 | Laporan & PDF (termasuk breakdown per event untuk superadmin) |
| 6 | Helpdesk, user guide |
| 7 | Polish mobile UX |

---

## 8. Sisa Pertanyaan Kecil (opsional, tidak blocking)
- Saat Super Admin klik "Activate Event Baru", apakah user/admin yang sedang login di event lama otomatis logout, atau tetap bisa lihat (read-only) event lamanya sampai logout manual? (Disarankan: read-only, tidak perlu paksa logout.)
- Format alasan cancel: fixed dropdown + "lainnya" (seperti dirancang di 3.4) sudah cukup, atau perlu kategori tambahan seperti "Duplikat transaksi"? (Bisa ditambah gampang, tidak mengubah struktur.)

*Dokumen pendamping: `PRD-Frontend-POS-Kasir-UMKM-Event.md` dan `PRD-Backend-POS-Kasir-UMKM-Event.md` berisi scope kerja terpisah untuk masing-masing fase.*
