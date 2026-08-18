@extends('layouts.app')

@section('title', 'Panduan Penggunaan Kasir UMKM')

@section('content')
<div x-data="{
    openFaq: 1,
    activeTab: 'flow'
}" class="max-w-4xl mx-auto space-y-6">

    <!-- Header Section (Twitter UI) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <span class="px-3.5 py-1 rounded-full bg-[#e8f5fd] text-[#1d9bf0] font-black text-xs border border-[#bde2f9] inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                Pusat Edukasi & SOP Stand
            </span>
            <h2 class="text-2xl sm:text-3xl font-black text-[#0f1419] tracking-tight mt-2">Panduan Operasional Kasir Stand</h2>
            <p class="text-xs sm:text-sm text-[#536471] font-semibold mt-1">Panduan lengkap alur penjualan, pembayaran QRIS & Tunai, penyetoran kas, dan bagi hasil</p>
        </div>

        <!-- Quick Access to Kasir -->
        <a 
            href="/user/kasir" 
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs sm:text-sm font-black shadow-md shadow-[#1d9bf0]/20 transition-all active:scale-95 cursor-pointer self-start sm:self-auto"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>Buka Terminal Kasir</span>
        </a>
    </div>

    <!-- Readonly Banner -->
    <div x-show="!$store.app.activeStoreEventActive" x-cloak class="p-4 rounded-2xl bg-[#f4212e]/10 border border-[#f4212e]/20 flex gap-3">
        <svg class="w-5 h-5 text-[#f4212e] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <div>
            <h3 class="text-sm font-black text-[#f4212e]">Mode Readonly (Event Tidak Aktif)</h3>
            <p class="text-xs text-[#f4212e] mt-1 font-medium">Event untuk warung ini telah selesai. Anda tetap dapat membaca panduan dan melihat riwayat data transaksi di menu Laporan.</p>
        </div>
    </div>

    <!-- 3 Core Rule Highlight Badges -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
        <!-- 1. Bagi Hasil -->
        <div class="bg-white rounded-3xl p-4.5 border border-[#eff3f4] shadow-xs flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-2xl bg-[#e8f5fd] text-[#1d9bf0] flex items-center justify-center font-black shrink-0">
                💰
            </div>
            <div>
                <h4 class="font-black text-sm text-[#0f1419]">Bagi Hasil 75% : 25%</h4>
                <p class="text-xs text-[#536471] font-medium mt-0.5 leading-relaxed">
                    <strong>75%</strong> Hak Bersih Warung & <strong>25%</strong> Porsi Panitia EO dari total transaksi berstatus Paid.
                </p>
            </div>
        </div>

        <!-- 2. QRIS Auto-Paid -->
        <div class="bg-white rounded-3xl p-4.5 border border-[#eff3f4] shadow-xs flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-[#00ba7c] flex items-center justify-center font-black shrink-0">
                ⚡
            </div>
            <div>
                <h4 class="font-black text-sm text-[#0f1419]">QRIS Langsung Sukses</h4>
                <p class="text-xs text-[#536471] font-medium mt-0.5 leading-relaxed">
                    Transaksi QRIS otomatis berstatus <strong>Paid</strong> saat dikonfirmasi. Unggah bukti transfer bersifat opsional/arsip.
                </p>
            </div>
        </div>

        <!-- 3. Setoran Cash -->
        <div class="bg-white rounded-3xl p-4.5 border border-[#eff3f4] shadow-xs flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-2xl bg-amber-50 text-[#ff7a00] flex items-center justify-center font-black shrink-0">
                💵
            </div>
            <div>
                <h4 class="font-black text-sm text-[#0f1419]">Setoran Uang Tunai</h4>
                <p class="text-xs text-[#536471] font-medium mt-0.5 leading-relaxed">
                    Transaksi Tunai berstatus <strong>Pending</strong> sampai uang fisik disetor & diverifikasi panitia EO di sistem.
                </p>
            </div>
        </div>
    </div>

    <!-- Navigation Switcher (Alur Kerja vs Komparasi Pembayaran) -->
    <div class="flex items-center gap-2 border-b border-[#eff3f4] pb-2">
        <button 
            @click="activeTab = 'flow'" 
            class="px-4 py-2 rounded-full text-xs font-black transition-all cursor-pointer"
            :class="activeTab === 'flow' ? 'bg-[#1d9bf0] text-white shadow-xs' : 'text-[#536471] hover:text-[#0f1419] hover:bg-[#eff3f4]'"
        >
            🚀 5 Langkah Alur Penjualan
        </button>
        <button 
            @click="activeTab = 'comparison'" 
            class="px-4 py-2 rounded-full text-xs font-black transition-all cursor-pointer"
            :class="activeTab === 'comparison' ? 'bg-[#1d9bf0] text-white shadow-xs' : 'text-[#536471] hover:text-[#0f1419] hover:bg-[#eff3f4]'"
        >
            🔄 Perbedaan Alur QRIS vs Tunai
        </button>
    </div>

    <!-- TAB 1: 5 Langkah Alur Penjualan Praktis -->
    <div x-show="activeTab === 'flow'" x-cloak class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- Step 1 -->
            <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs space-y-2.5 relative flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="w-7 h-7 rounded-full bg-[#1d9bf0] text-white font-black text-xs flex items-center justify-center">1</span>
                        <span class="text-[10px] font-black uppercase text-[#1d9bf0] bg-[#e8f5fd] px-2.5 py-0.5 rounded-full">Menu Produk</span>
                    </div>
                    <h4 class="font-black text-[#0f1419] text-base mt-2.5">Kelola Menu & Kategori Produk</h4>
                    <p class="text-xs text-[#536471] font-medium mt-1 leading-relaxed">
                        Buka menu <strong>Produk</strong> untuk menambah atau mengubah menu. Masukkan nama makanan, minuman, snack, atau merchandise, harga jual, serta foto produk. Anda juga dapat menandai produk sebagai <em>Best Seller</em> atau <em>Favorit</em>.
                    </p>
                </div>
                <div class="pt-2 border-t border-[#eff3f4]/80 flex items-center justify-between text-[11px]">
                    <span class="text-[#536471] font-semibold">Tersedia 4 Kategori</span>
                    <a href="/user/produk" class="text-[#1d9bf0] font-black hover:underline inline-flex items-center gap-1">
                        Buka Menu Produk &rarr;
                    </a>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs space-y-2.5 relative flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="w-7 h-7 rounded-full bg-[#1d9bf0] text-white font-black text-xs flex items-center justify-center">2</span>
                        <span class="text-[10px] font-black uppercase text-[#1d9bf0] bg-[#e8f5fd] px-2.5 py-0.5 rounded-full">Terminal Kasir</span>
                    </div>
                    <h4 class="font-black text-[#0f1419] text-base mt-2.5">Pilih Pesanan Pelanggan</h4>
                    <p class="text-xs text-[#536471] font-medium mt-1 leading-relaxed">
                        Buka menu <strong>Kasir & POS</strong>. Ketuk foto menu yang dipesan pelanggan untuk memasukkan ke keranjang belanja. Gunakan filter kategori atau kolom pencarian untuk mempercepat layanan, lalu sesuaikan jumlah (qty) pesanan.
                    </p>
                </div>
                <div class="pt-2 border-t border-[#eff3f4]/80 flex items-center justify-between text-[11px]">
                    <span class="text-[#536471] font-semibold">Bisa Cari Menu Cepat</span>
                    <a href="/user/kasir" class="text-[#1d9bf0] font-black hover:underline inline-flex items-center gap-1">
                        Menuju Kasir &rarr;
                    </a>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs space-y-2.5 relative flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="w-7 h-7 rounded-full bg-[#1d9bf0] text-white font-black text-xs flex items-center justify-center">3</span>
                        <span class="text-[10px] font-black uppercase text-[#00ba7c] bg-emerald-50 px-2.5 py-0.5 rounded-full">Metode Pembayaran</span>
                    </div>
                    <h4 class="font-black text-[#0f1419] text-base mt-2.5">Pilih Pembayaran: QRIS atau Tunai</h4>
                    <div class="space-y-1.5 text-xs text-[#536471] font-medium mt-1">
                        <p>
                            📱 <strong>QRIS:</strong> Pembeli scan kode QR. Jika stand mengaktifkan Dynamic QRIS, nominal + kode unik stand otomatis terisi. Tekan <em>"Bayar & Cetak Nota Otomatis"</em> &rarr; transaksi langsung <strong>Paid (Sukses)</strong>. (Upload bukti foto opsional).
                        </p>
                        <p>
                            💵 <strong>Cash / Tunai:</strong> Masukkan uang tunai diterima atau gunakan tombol preset nominal cepat. Sistem menghitung kembalian live. Tekan <em>"Bayar Tunai & Cetak Nota"</em> &rarr; transaksi berstatus <strong>Pending</strong>.
                        </p>
                    </div>
                </div>
                <div class="pt-2 border-t border-[#eff3f4]/80 flex items-center justify-between text-[11px]">
                    <span class="text-[#536471] font-semibold">Kalkulator Kembalian Otomatis</span>
                    <span class="text-[#00ba7c] font-black">Instan & Akurat</span>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs space-y-2.5 relative flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="w-7 h-7 rounded-full bg-[#1d9bf0] text-white font-black text-xs flex items-center justify-center">4</span>
                        <span class="text-[10px] font-black uppercase text-[#ff7a00] bg-amber-50 px-2.5 py-0.5 rounded-full">Setor Tunai</span>
                    </div>
                    <h4 class="font-black text-[#0f1419] text-base mt-2.5">Setor Kas Fisik ke Panitia EO</h4>
                    <p class="text-xs text-[#536471] font-medium mt-1 leading-relaxed">
                        Kumpulkan uang fisik hasil transaksi tunai di kasir stand Anda. Setorkan uang fisik tersebut ke Panitia EO (Kasir Utama / Tenda Panitia). Setelah panitia memverifikasi melalui menu <strong>Verifikasi Cash</strong>, transaksi tunai Anda berubah menjadi <strong>Paid</strong> dan masuk ke perhitungan bagi hasil 75%.
                    </p>
                </div>
                <div class="pt-2 border-t border-[#eff3f4]/80 flex items-center justify-between text-[11px]">
                    <span class="text-[#536471] font-semibold">Dilakukan Berkala / Tutup Shift</span>
                    <span class="text-[#ff7a00] font-black">Wajib Diverifikasi EO</span>
                </div>
            </div>

            <!-- Step 5 (Full Width) -->
            <div class="md:col-span-2 bg-gradient-to-br from-[#1d9bf0]/10 to-[#1d9bf0]/5 rounded-3xl p-5 border border-[#bde2f9] shadow-xs space-y-2.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-full bg-[#1d9bf0] text-white font-black text-xs flex items-center justify-center">5</span>
                        <h4 class="font-black text-[#0f1419] text-base">Cetak Struk & Ekspor Laporan Penjualan</h4>
                    </div>
                    <p class="text-xs text-[#536471] font-medium mt-1 leading-relaxed max-w-2xl">
                        Struk pembayaran dapat langsung dicetak ke printer thermal bluetooth/USB atau disimpan sebagai PDF. Pantau pendapatan bersih 75% Anda secara real-time di menu <strong>Laporan Saya</strong> dan unduh dokumen rekapitulasi dalam format <strong>PDF</strong>, <strong>Word (.doc)</strong>, atau <strong>Excel (.xls)</strong>.
                    </p>
                </div>
                <a href="/user/laporan" class="px-4 py-2.5 rounded-full bg-[#0f1419] hover:bg-[#272c30] text-white font-black text-xs shrink-0 transition-colors shadow-xs">
                    Lihat Laporan &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- TAB 2: Perbandingan Alur QRIS vs Tunai (Visual Comparison Table) -->
    <div x-show="activeTab === 'comparison'" x-cloak class="bg-white rounded-3xl border border-[#eff3f4] overflow-hidden shadow-xs">
        <div class="p-5 border-b border-[#eff3f4]">
            <h3 class="font-black text-base text-[#0f1419]">Perbandingan Alur Pembayaran QRIS vs Tunai</h3>
            <p class="text-xs text-[#536471] font-medium mt-0.5">Memahami perbedaan siklus verifikasi uang dan hak bagi hasil</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-[#f7f9f9] text-[#0f1419] font-black border-b border-[#eff3f4]">
                        <th class="p-3.5 sm:p-4">Komponen</th>
                        <th class="p-3.5 sm:p-4 text-[#1d9bf0]">📱 Pembayaran QRIS</th>
                        <th class="p-3.5 sm:p-4 text-[#ff7a00]">💵 Pembayaran Tunai (Cash)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eff3f4] font-medium text-[#0f1419]">
                    <tr>
                        <td class="p-3.5 sm:p-4 font-bold bg-[#f7f9f9]/50 text-[#536471]">Status Awal Transaksi</td>
                        <td class="p-3.5 sm:p-4 font-black text-[#00ba7c]">
                            <span class="inline-flex items-center gap-1 bg-emerald-50 text-[#00ba7c] px-2.5 py-1 rounded-full text-[11px] font-black border border-emerald-200">
                                ✓ Paid (Langsung Sukses)
                            </span>
                        </td>
                        <td class="p-3.5 sm:p-4 font-black text-[#ff7a00]">
                            <span class="inline-flex items-center gap-1 bg-amber-50 text-[#ff7a00] px-2.5 py-1 rounded-full text-[11px] font-black border border-amber-200">
                                ⏳ Pending (Menunggu Setor)
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="p-3.5 sm:p-4 font-bold bg-[#f7f9f9]/50 text-[#536471]">Penyimpanan Uang Masuk</td>
                        <td class="p-3.5 sm:p-4">Masuk langsung ke rekening penampung resmi EO</td>
                        <td class="p-3.5 sm:p-4">Uang tunai fisik diterima & dipegang kasir stand</td>
                    </tr>
                    <tr>
                        <td class="p-3.5 sm:p-4 font-bold bg-[#f7f9f9]/50 text-[#536471]">Kode Unik Transaksi</td>
                        <td class="p-3.5 sm:p-4">Otomatis ditambahkan sesuai ID Stand untuk identifikasi transaksi</td>
                        <td class="p-3.5 sm:p-4">Tidak memerlukan kode unik (sesuai nominal harga belanja)</td>
                    </tr>
                    <tr>
                        <td class="p-3.5 sm:p-4 font-bold bg-[#f7f9f9]/50 text-[#536471]">Unggah Bukti Foto</td>
                        <td class="p-3.5 sm:p-4"><strong>Opsional</strong> (sebagai arsip/dokumentasi internal stand)</td>
                        <td class="p-3.5 sm:p-4">Tidak diperlukan (kalkulator kembalian otomatis)</td>
                    </tr>
                    <tr>
                        <td class="p-3.5 sm:p-4 font-bold bg-[#f7f9f9]/50 text-[#536471]">Proses Verifikasi</td>
                        <td class="p-3.5 sm:p-4">Otomatis di sistem (tidak perlu antre verifikasi panitia)</td>
                        <td class="p-3.5 sm:p-4">Stand menyetorkan uang fisik &rarr; Panitia memverifikasi di menu <strong>Verifikasi Cash</strong></td>
                    </tr>
                    <tr>
                        <td class="p-3.5 sm:p-4 font-bold bg-[#f7f9f9]/50 text-[#536471]">Pencatatan Bagi Hasil 75%</td>
                        <td class="p-3.5 sm:p-4">Langsung dihitung dan masuk ke pendapatan bersih di Laporan</td>
                        <td class="p-3.5 sm:p-4">Dihitung dan masuk ke pendapatan bersih <strong>setelah disetujui panitia EO</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- FAQ Accordion (Twitter UI) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#eff3f4] shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-[#eff3f4]">
            <div>
                <h3 class="font-black text-lg text-[#0f1419]">Pertanyaan Sering Diajukan (FAQ)</h3>
                <p class="text-xs text-[#536471] font-semibold mt-0.5">Solusi cepat untuk kendala dan pertanyaan umum seputar sistem kasir</p>
            </div>
            <span class="text-xs font-black text-[#1d9bf0] bg-[#e8f5fd] px-3 py-1 rounded-full">7 Topik Panduan</span>
        </div>

        <!-- FAQ 1: Bagi Hasil -->
        <div class="border-b border-[#eff3f4] pb-3.5">
            <button 
                @click="openFaq = (openFaq === 1 ? null : 1)"
                class="w-full flex items-center justify-between text-left font-black text-sm text-[#0f1419] hover:text-[#1d9bf0] py-1 cursor-pointer"
            >
                <span class="flex items-center gap-2">
                    <span class="text-[#1d9bf0]">01.</span>
                    Bagaimana skema dan pembagian bagi hasil di event ini?
                </span>
                <svg class="w-4 h-4 text-[#536471] transition-transform duration-200" :class="{'rotate-180': openFaq === 1}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="openFaq === 1" x-cloak x-transition class="mt-2.5 text-xs text-[#536471] leading-relaxed space-y-2 pl-6">
                <p>Dari setiap transaksi penjualan yang telah berhasil (berstatus <strong>Paid</strong>):</p>
                <ul class="list-disc pl-5 space-y-1 text-[#0f1419] font-medium">
                    <li><strong>75% dari Total Omzet</strong> merupakan hak bersih milik Pemilik Warung / Stand.</li>
                    <li><strong>25% dari Total Omzet</strong> merupakan bagian operasional dan manajemen Panitia EO.</li>
                </ul>
                <p class="text-[11px] text-[#536471] italic">
                    *Rincian pembagian omzet dan pendapatan bersih Anda dapat dipantau transparan secara real-time di halaman Laporan maupun pada lembar struk transaksi.
                </p>
            </div>
        </div>

        <!-- FAQ 2: Kenapa QRIS Langsung Paid -->
        <div class="border-b border-[#eff3f4] pb-3.5">
            <button 
                @click="openFaq = (openFaq === 2 ? null : 2)"
                class="w-full flex items-center justify-between text-left font-black text-sm text-[#0f1419] hover:text-[#1d9bf0] py-1 cursor-pointer"
            >
                <span class="flex items-center gap-2">
                    <span class="text-[#1d9bf0]">02.</span>
                    Mengapa transaksi QRIS langsung berstatus Paid (Auto-Success)?
                </span>
                <svg class="w-4 h-4 text-[#536471] transition-transform duration-200" :class="{'rotate-180': openFaq === 2}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="openFaq === 2" x-cloak x-transition class="mt-2.5 text-xs text-[#536471] leading-relaxed space-y-2 pl-6">
                <p>
                    Untuk memastikan alur pelayanan kasir di stand berjalan cepat tanpa menyebabkan antrean panjang bagi pembeli, sistem POS menerapkan alur <strong>Auto-Paid</strong> untuk pembayaran QRIS.
                </p>
                <p>
                    Ketika pelanggan telah menunjukkan bukti pembayaran berhasil pada aplikasi m-banking / e-wallet mereka, kasir cukup menekan <strong>"Bayar & Cetak Nota Otomatis"</strong>. Transaksi langsung tercatat sukses dan struk dapat langsung dicetak. Anda juga dapat memotret struk/screenshot pembayaran lewat tombol kamera sebagai lampiran arsip (opsional).
                </p>
            </div>
        </div>

        <!-- FAQ 3: Kenapa Cash Pending -->
        <div class="border-b border-[#eff3f4] pb-3.5">
            <button 
                @click="openFaq = (openFaq === 3 ? null : 3)"
                class="w-full flex items-center justify-between text-left font-black text-sm text-[#0f1419] hover:text-[#1d9bf0] py-1 cursor-pointer"
            >
                <span class="text-[#1d9bf0]">03.</span>
                Mengapa transaksi Tunai (Cash) berstatus Pending setelah checkout?
            </button>
            <div x-show="openFaq === 3" x-cloak x-transition class="mt-2.5 text-xs text-[#536471] leading-relaxed space-y-2 pl-6">
                <p>
                    Pada transaksi tunai, uang fisik kas dipegang langsung oleh kasir stand Anda saat transaksi berlangsung. Status <strong>Pending</strong> menandakan bahwa uang kas tersebut belum diserahkan ke bendahara panitia EO.
                </p>
                <p>
                    <strong>Prosedur Setor Tunai:</strong> Kasir mengumpulkan kas fisik & menyerahkannya ke tenda panitia EO secara berkala. Panitia akan membuka menu <em>Verifikasi Cash</em> dan mengonfirmasi nominal yang diterima. Setelah dikonfirmasi panitia, status transaksi langsung berubah menjadi <strong>Paid</strong> dan pendapatan bersih porsi 75% Anda otomatis terbukukan.
                </p>
            </div>
        </div>

        <!-- FAQ 4: Fungsi Kode Unik -->
        <div class="border-b border-[#eff3f4] pb-3.5">
            <button 
                @click="openFaq = (openFaq === 4 ? null : 4)"
                class="w-full flex items-center justify-between text-left font-black text-sm text-[#0f1419] hover:text-[#1d9bf0] py-1 cursor-pointer"
            >
                <span class="flex items-center gap-2">
                    <span class="text-[#1d9bf0]">04.</span>
                    Apa fungsi Kode Unik pada total tagihan QRIS?
                </span>
                <svg class="w-4 h-4 text-[#536471] transition-transform duration-200" :class="{'rotate-180': openFaq === 4}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="openFaq === 4" x-cloak x-transition class="mt-2.5 text-xs text-[#536471] leading-relaxed space-y-2 pl-6">
                <p>
                    Kode unik diambil dari <strong>ID Toko / Nomor Identifikasi Stand</strong> Anda (misal Stand ID 12 menambahkan Rp12 pada total belanja).
                </p>
                <p>
                    Kode unik ini sangat penting untuk membedakan transaksi antar stand yang menjual menu dengan nominal sama, sehingga panitia EO dapat melakukan rekonsiliasi mutasi rekening bank secara tepat dan tidak tertukar. Jika menggunakan Dynamic QRIS, kode unik ini tertanam secara otomatis dalam barcode QR.
                </p>
            </div>
        </div>

        <!-- FAQ 5: Cetak Struk -->
        <div class="border-b border-[#eff3f4] pb-3.5">
            <button 
                @click="openFaq = (openFaq === 5 ? null : 5)"
                class="w-full flex items-center justify-between text-left font-black text-sm text-[#0f1419] hover:text-[#1d9bf0] py-1 cursor-pointer"
            >
                <span class="flex items-center gap-2">
                    <span class="text-[#1d9bf0]">05.</span>
                    Bagaimana cara mencetak struk transaksi ke printer thermal?
                </span>
                <svg class="w-4 h-4 text-[#536471] transition-transform duration-200" :class="{'rotate-180': openFaq === 5}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="openFaq === 5" x-cloak x-transition class="mt-2.5 text-xs text-[#536471] leading-relaxed space-y-2 pl-6">
                <p>
                    Setelah proses checkout selesai, pop-up nota pembayaran akan otomatis terbuka di layar. Anda dapat:
                </p>
                <ul class="list-disc pl-5 space-y-1 text-[#0f1419] font-medium">
                    <li>Menekan tombol <strong>"Cetak Struk"</strong> untuk mencetak langsung ke Printer Thermal (USB / Bluetooth) ukuran 58mm atau 80mm.</li>
                    <li>Membagikan tautan struk online kepada pembeli melalui WhatsApp jika pembeli tidak memerlukan struk fisik.</li>
                    <li>Mencetak ulang kapan saja lewat menu <strong>Laporan Saya</strong> dengan menekan tombol nota pada baris transaksi yang diinginkan.</li>
                </ul>
            </div>
        </div>

        <!-- FAQ 6: Pembatalan Pesanan -->
        <div class="border-b border-[#eff3f4] pb-3.5">
            <button 
                @click="openFaq = (openFaq === 6 ? null : 6)"
                class="w-full flex items-center justify-between text-left font-black text-sm text-[#0f1419] hover:text-[#1d9bf0] py-1 cursor-pointer"
            >
                <span class="flex items-center gap-2">
                    <span class="text-[#1d9bf0]">06.</span>
                    Bagaimana jika terjadi salah input atau pembeli membatalkan pesanan?
                </span>
                <svg class="w-4 h-4 text-[#536471] transition-transform duration-200" :class="{'rotate-180': openFaq === 6}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="openFaq === 6" x-cloak x-transition class="mt-2.5 text-xs text-[#536471] leading-relaxed space-y-2 pl-6">
                <p>
                    Untuk menjaga keabsahan audit pembukuan dan rekonsiliasi kas, pembatalan transaksi berstatus <strong>Paid</strong> hanya dapat dilakukan oleh Panitia EO melalui otorisasi Admin.
                </p>
                <p>
                    Jika terjadi kesalahan input atau pembatalan, segera buat tiket bantuan di menu <strong>Helpdesk</strong> atau hubungi langsung panitia di tenda EO dengan menyebutkan <strong>Nomor Invoice</strong> transaksi terkait.
                </p>
            </div>
        </div>

        <!-- FAQ 7: Mode Testing -->
        <div>
            <button 
                @click="openFaq = (openFaq === 7 ? null : 7)"
                class="w-full flex items-center justify-between text-left font-black text-sm text-[#0f1419] hover:text-[#1d9bf0] py-1 cursor-pointer"
            >
                <span class="flex items-center gap-2">
                    <span class="text-[#1d9bf0]">07.</span>
                    Apa itu Mode Testing (Simulasi / Uji Coba Transaksi)?
                </span>
                <svg class="w-4 h-4 text-[#536471] transition-transform duration-200" :class="{'rotate-180': openFaq === 7}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="openFaq === 7" x-cloak x-transition class="mt-2.5 text-xs text-[#536471] leading-relaxed space-y-2 pl-6">
                <p>
                    Sebelum event resmi dibuka, Panitia EO dapat mengaktifkan <strong>Masa Testing</strong>. Pada mode ini, kasir stand dapat mencoba membuat transaksi simulasi secara bebas untuk melatih kelancaran kasir.
                </p>
                <p>
                    Transaksi pada masa testing ditandai dengan badge <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 font-bold border border-amber-200 text-[10px]">🧪 Testing</span> dan tidak akan mencemari data keuangan riil event. Panitia dapat membersihkan / mereset transaksi testing tersebut sebelum acara resmi dimulai.
                </p>
            </div>
        </div>

    </div>

    <!-- Quick Help & Support Footer Banner -->
    <div class="bg-white rounded-3xl p-6 border border-[#eff3f4] shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-[#e8f5fd] text-[#1d9bf0] flex items-center justify-center font-black shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div>
                <h4 class="font-black text-sm text-[#0f1419]">Butuh Bantuan Selama Event Berlangsung?</h4>
                <p class="text-xs text-[#536471] font-semibold mt-0.5">Kirimkan tiket bantuan langsung ke panitia EO untuk respon cepat</p>
            </div>
        </div>
        <a 
            href="/user/helpdesk" 
            class="px-5 py-2.5 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white font-black text-xs transition-all shadow-md shadow-[#1d9bf0]/20 shrink-0 cursor-pointer active:scale-95"
        >
            Buka Helpdesk Stand
        </a>
    </div>

</div>
@endsection
