@extends('layouts.app')

@section('title', 'Panduan Penggunaan Kasir UMKM')

@section('content')
<div x-data="{
    openFaq: 1
}" class="max-w-4xl mx-auto space-y-8">

    <!-- Header (Twitter UI) -->
    <div class="text-center sm:text-left">
        <span class="px-3.5 py-1 rounded-full bg-[#e8f5fd] text-[#1d9bf0] font-bold text-xs border border-[#bde2f9]">Pusat Edukasi Stand</span>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-[#0f1419] tracking-tight mt-2">Panduan Kasir & Operasional Event</h2>
        <p class="text-xs sm:text-sm text-[#536471] mt-1">Langkah praktis penggunaan aplikasi POS untuk pemilik warung dan kasir stand</p>
    </div>

    <!-- 4 Step Quick Start Visual Guide (Twitter UI) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Step 1 -->
        <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs relative">
            <span class="w-7 h-7 rounded-full bg-[#1d9bf0] text-white font-black text-xs flex items-center justify-center mb-3">1</span>
            <h4 class="font-bold text-[#0f1419] text-sm">Input Menu Produk</h4>
            <p class="text-xs text-[#536471] mt-1.5 leading-relaxed">Buka menu Produk, masukkan foto, nama makanan/minuman, dan harga jual yang akan tampil di kasir.</p>
        </div>

        <!-- Step 2 -->
        <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs relative">
            <span class="w-7 h-7 rounded-full bg-[#1d9bf0] text-white font-black text-xs flex items-center justify-center mb-3">2</span>
            <h4 class="font-bold text-[#0f1419] text-sm">Pilih Pesanan Pembeli</h4>
            <p class="text-xs text-[#536471] mt-1.5 leading-relaxed">Ketuk menu di layar Kasir untuk memasukkan pesanan ke keranjang. Sesuaikan jumlah item pesanan.</p>
        </div>

        <!-- Step 3 -->
        <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs relative">
            <span class="w-7 h-7 rounded-full bg-[#1d9bf0] text-white font-black text-xs flex items-center justify-center mb-3">3</span>
            <h4 class="font-bold text-[#0f1419] text-sm">Pilih Cash / QRIS</h4>
            <p class="text-xs text-[#536471] mt-1.5 leading-relaxed">Untuk Tunai: masukkan uang diterima dan hitung kembalian live. Untuk QRIS: scan QRIS dan unggah bukti transfer.</p>
        </div>

        <!-- Step 4 -->
        <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs relative">
            <span class="w-7 h-7 rounded-full bg-[#1d9bf0] text-white font-black text-xs flex items-center justify-center mb-3">4</span>
            <h4 class="font-bold text-[#0f1419] text-sm">Cetak Struk & Cek Share</h4>
            <p class="text-xs text-[#536471] mt-1.5 leading-relaxed">Cetak struk belanja pembeli dan pantau pendapatan bersih porsi 75% Anda secara real-time di Laporan.</p>
        </div>
    </div>

    <!-- FAQ Accordion (Twitter UI) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#eff3f4] shadow-xs space-y-4">
        <h3 class="font-bold text-lg text-[#0f1419] pb-2 border-b border-[#eff3f4]">Pertanyaan Sering Diajukan (FAQ)</h3>

        <!-- FAQ 1 -->
        <div class="border-b border-[#eff3f4] pb-3">
            <button 
                @click="openFaq = (openFaq === 1 ? null : 1)"
                class="w-full flex items-center justify-between text-left font-bold text-sm text-[#0f1419] hover:text-[#1d9bf0] py-1"
            >
                <span>Bagaimana skema bagi hasil penjualan di event ini?</span>
                <span class="text-[#536471]" x-text="openFaq === 1 ? '▲' : '▼'"></span>
            </button>
            <div x-show="openFaq === 1" x-transition class="mt-2 text-xs text-[#536471] leading-relaxed space-y-1">
                <p>Dari total transaksi yang berhasil (Paid):</p>
                <ul class="list-disc pl-5 space-y-0.5 text-[#0f1419] font-medium">
                    <li><strong>75%</strong> menjadi hak bersih Pemilik Warung.</li>
                    <li><strong>25%</strong> menjadi hak Panitia EO (dipotong flat Rp1.000 untuk lisensi platform Super Admin).</li>
                </ul>
            </div>
        </div>

        <!-- FAQ 2 -->
        <div class="border-b border-[#eff3f4] pb-3">
            <button 
                @click="openFaq = (openFaq === 2 ? null : 2)"
                class="w-full flex items-center justify-between text-left font-bold text-sm text-[#0f1419] hover:text-[#1d9bf0] py-1"
            >
                <span>Kenapa transaksi QRIS berstatus Pending setelah diunggah?</span>
                <span class="text-[#536471]" x-text="openFaq === 2 ? '▲' : '▼'"></span>
            </button>
            <div x-show="openFaq === 2" x-transition class="mt-2 text-xs text-[#536471] leading-relaxed">
                <p>Karena QRIS yang digunakan adalah 1 rekening resmi EO untuk mempermudah pengunjung, panitia EO akan memverifikasi mutasi bank dan bukti screenshot Anda terlebih dahulu. Setelah disetujui, status transaksi akan otomatis berubah menjadi <strong>Paid</strong>.</p>
            </div>
        </div>

        <!-- FAQ 3 -->
        <div class="border-b border-[#eff3f4] pb-3">
            <button 
                @click="openFaq = (openFaq === 3 ? null : 3)"
                class="w-full flex items-center justify-between text-left font-bold text-sm text-[#0f1419] hover:text-[#1d9bf0] py-1"
            >
                <span>Bagaimana jika terjadi salah input harga atau pembeli membatalkan pesanan?</span>
                <span class="text-[#536471]" x-text="openFaq === 3 ? '▲' : '▼'"></span>
            </button>
            <div x-show="openFaq === 3" x-transition class="mt-2 text-xs text-[#536471] leading-relaxed">
                <p>Untuk menjaga transparansi audit, pembatalan transaksi berstatus Paid hanya dapat dilakukan oleh Panitia EO melalui menu Pembatalan Transaksi. Laporkan ke panitia melalui menu <strong>Helpdesk</strong> atau hubungi langsung tenda panitia.</p>
            </div>
        </div>
    </div>
</div>
@endsection
