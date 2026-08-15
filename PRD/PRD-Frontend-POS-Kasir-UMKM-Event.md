# PRD FRONTEND — POS Kasir UMKM Event
**Scope:** Fase 1 — seluruh tampilan jalan dengan **data dummy**, tanpa logic backend nyata.
**Referensi lengkap business rule:** lihat `PRD-Full-POS-Kasir-UMKM-Event.md`
**Stack asumsi:** Blade + Livewire/Alpine.js + Tailwind CSS

---

## 1. Prinsip Umum Frontend
- Mobile-first, semua halaman harus nyaman dipakai 1 tangan di HP.
- **Desktop (≥ `lg` breakpoint):** sidebar kiri persisten.
- **Mobile (< `lg`):** bottom navigation bar, ikon utama sesuai role. Untuk role User, ikon **Kasir/Checkout dibuat menonjol** (lebih besar/di tengah, mis. FAB style) karena paling sering dipakai saat event berlangsung.
- Semua tabel di desktop → jadi **card list** di mobile (bukan scroll horizontal).
- Bahasa Indonesia di seluruh UI, termasuk pesan error & alert.
- Semua data di fase ini adalah **array dummy statis** (di controller/Blade/Livewire component) — tidak ada query database.

---

## 2. Halaman & Komponen per Role

### 2.1 Auth
- **Login**: email, password, checkbox "Ingat saya", tombol submit. Dummy: redirect ke dashboard sesuai role hardcode pilihan (untuk keperluan demo/testing UI, boleh pakai toggle role sementara).
- **Register** (khusus User/pemilik warung):
  - Field: nama lengkap, username, nama toko, nomor telp/WA, password, konfirmasi password.
  - **Password strength meter** — live update (Alpine.js) saat mengetik, 3–4 tingkat (lemah/sedang/kuat/sangat kuat), indikator visual (bar warna).
  - Validasi client-side: konfirmasi password harus match, tampil real-time.
  - Tidak ada langkah approval — submit langsung "berhasil daftar, silakan login".

### 2.2 User (Pemilik Warung)
| Halaman | Komponen Wajib | Data Dummy |
|---|---|---|
| Produk (list) | Grid/list produk (foto, judul, harga), tombol tambah, tombol edit/hapus per item | 5–6 produk dummy |
| Tambah/Edit Produk | Modal atau halaman terpisah — upload foto (preview), judul, harga | — |
| Kasir/Checkout | Grid produk untuk ditambah ke keranjang, badge jumlah keranjang, tombol buka checkout | pakai produk dummy yang sama |
| **Panel Checkout (slide-over dari kanan)** | List item keranjang (qty +/-, hapus), subtotal, toggle Cash/QRIS | — |
| — Tab Cash | Input "Uang Diterima", tampilan otomatis "Kembalian" (hitung live di JS: `amount_paid - total`), tombol konfirmasi disabled kalau uang diterima < total | — |
| — Tab QRIS | Gambar QRIS statis (dummy image), nominal total, tombol upload bukti (image preview), tombol "Konfirmasi Pembayaran" | — |
| — Alert setelah submit QRIS | Modal/toast: **"Bukti terkirim, menunggu verifikasi admin — transaksi belum berhasil"** (bukan langsung sukses) | — |
| — Alert setelah submit Cash | Toast sukses langsung: "Transaksi berhasil, kembalian Rp{x}" | — |
| Laporan | Tabel riwayat transaksi: tanggal, invoice, metode, total, **uang diterima & kembalian (khusus cash)**, status (paid/pending/rejected/cancelled — beda warna badge), tombol download PDF (dummy link) | 6–8 transaksi dummy dengan variasi status |
| Helpdesk | List tiket dummy + status badge, form buat tiket baru (subjek, pesan) | 2–3 tiket dummy |
| User Guide | Halaman statis (accordion FAQ atau step-by-step) | konten placeholder |

### 2.3 Admin (EO)
| Halaman | Komponen Wajib | Data Dummy |
|---|---|---|
| Dashboard | Card ringkasan: total transaksi hari ini, total revenue event aktif, jumlah warung aktif, jumlah transaksi menunggu verifikasi (dengan link cepat), grafik sederhana (bar/line, boleh pakai Chart.js dummy) | angka dummy |
| Produk semua warung | Tabel/list read-only, filter dropdown per warung, search | 8–10 produk dummy lintas warung |
| Warung & Pemilik | List warung: nama toko, nama pemilik, kontak, jumlah produk, status aktif | 4–5 warung dummy |
| Laporan Full | Tabel semua transaksi semua warung, filter (tanggal/warung/status), breakdown revenue (owner/admin/superadmin share) per baris, tombol lihat bukti bayar QRIS (modal image), tombol download PDF | 10+ transaksi dummy |
| **Verifikasi QRIS** | Antrian transaksi `pending_verification`: card/list dengan foto bukti, nominal, warung, tombol Approve/Reject (reject minta alasan singkat) | 2–3 dummy pending |
| **Cancel Transaksi** | Dari detail transaksi `paid`: tombol "Batalkan" → buka modal form: dropdown alasan (Salah input barang/harga, Barang dikembalikan, Kesalahan sistem, Lainnya), textarea catatan (wajib jika "Lainnya"), **checkbox wajib**: *"Saya konfirmasi refund/koordinasi ke warung sudah/akan ditangani manual di luar sistem"* — tombol "Batalkan Transaksi" **disabled sampai checkbox dicentang** | — |
| Helpdesk | List semua tiket dari semua warung, filter status, form balas | 3–4 tiket dummy |
| User Guide | Statis untuk admin | placeholder |

### 2.4 Super Admin
| Halaman | Komponen Wajib | Data Dummy |
|---|---|---|
| Dashboard | Sama seperti admin tapi scope semua event + pilihan event mana yang mau dilihat | dummy |
| **Kelola Event** | List semua event (nama, tanggal, status aktif/arsip), tombol "Buat Event Baru" (form: nama, tanggal mulai/selesai), tombol "Aktifkan" per event non-aktif (dengan konfirmasi "event aktif sebelumnya akan diarsipkan") | 2–3 event dummy (1 aktif, sisanya arsip) |
| Laporan | Sama seperti admin, plus filter/dropdown pilih event, plus baris breakdown revenue Super Admin (akumulasi flat Rp1.000/transaksi) | dummy |

---

## 3. Validasi & Interaksi Wajib (client-side, sudah harus jalan di fase dummy)
- Password strength meter — live, tanpa perlu request server.
- Kalkulasi kembalian cash — live, tanpa request server (`amount_paid - total_amount`, update tiap keystroke).
- Tombol checkout QRIS disabled sampai foto bukti diupload.
- Tombol cancel transaksi disabled sampai checkbox dicentang.
- Semua modal/slide-over pakai transisi halus (Alpine `x-transition` atau setara).

## 4. Checklist Selesai Fase Frontend
- [ ] Login & Register (dengan strength meter)
- [ ] User: Produk (CRUD dummy), Kasir + Checkout (cash & QRIS lengkap dengan kembalian & alert pending), Laporan, Helpdesk, User Guide
- [ ] Admin: Dashboard, Produk semua warung, Warung & Pemilik, Laporan full, Verifikasi QRIS, **Cancel transaksi (dengan checkbox)**, Helpdesk, User Guide
- [ ] Super Admin: Dashboard, **Kelola Event (create & activate)**, Laporan multi-event
- [ ] Sidebar desktop & bottom nav mobile untuk ketiga role, responsive teruji di breakpoint mobile/tablet/desktop
