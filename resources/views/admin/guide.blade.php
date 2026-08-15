@extends('layouts.app')

@section('title', 'SOP & Panduan Operasional EO')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Header (Twitter UI) -->
    <div class="text-center sm:text-left">
        <span class="px-3.5 py-1 rounded-full bg-[#0f1419] text-white font-bold text-xs">Handbook Panitia Event</span>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-[#0f1419] tracking-tight mt-2">SOP & Panduan Operasional Admin EO</h2>
        <p class="text-xs sm:text-sm text-[#536471] mt-1">Pedoman operasional kasir, verifikasi QRIS, dan pembatalan transaksi berstatus Paid</p>
    </div>

    <!-- 3 Core Operational Pillars (Twitter UI) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Pillar 1 -->
        <div class="bg-white rounded-3xl p-6 border border-[#eff3f4] shadow-xs space-y-2.5">
            <div class="w-10 h-10 rounded-full bg-amber-50 text-[#ff7a00] flex items-center justify-center font-black">
                📱
            </div>
            <h4 class="font-extrabold text-base text-[#0f1419]">1. Verifikasi QRIS</h4>
            <p class="text-xs text-[#536471] leading-relaxed">
                Selalu cocokkan nominal transfer dan jam transaksi pada screenshot pengunjung dengan notifikasi/mutasi m-banking rekening panitia sebelum menekan tombol <strong>Setujui (Paid)</strong>.
            </p>
        </div>

        <!-- Pillar 2 -->
        <div class="bg-white rounded-3xl p-6 border border-[#eff3f4] shadow-xs space-y-2.5">
            <div class="w-10 h-10 rounded-full bg-rose-50 text-[#f4212e] flex items-center justify-center font-black">
                🛑
            </div>
            <h4 class="font-extrabold text-base text-[#0f1419]">2. Pembatalan Paid</h4>
            <p class="text-xs text-[#536471] leading-relaxed">
                Sistem <strong>tidak memproses refund uang otomatis</strong>. Admin wajib berkoordinasi langsung dengan warung & customer secara manual di luar sistem sebelum mencentang konfirmasi pembatalan.
            </p>
        </div>

        <!-- Pillar 3 -->
        <div class="bg-white rounded-3xl p-6 border border-[#eff3f4] shadow-xs space-y-2.5">
            <div class="w-10 h-10 rounded-full bg-emerald-50 text-[#00ba7c] flex items-center justify-center font-black">
                💰
            </div>
            <h4 class="font-extrabold text-base text-[#0f1419]">3. Bagi Hasil 75/25</h4>
            <p class="text-xs text-[#536471] leading-relaxed">
                Porsi hak warung adalah 75% dari total transaksi Paid. Porsi EO adalah 25% dikurangi flat lisensi Rp1.000 per transaksi yang menjadi hak Super Admin.
            </p>
        </div>
    </div>

    <!-- Detailed Guidelines (Twitter UI) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#eff3f4] shadow-xs space-y-6 text-sm text-[#0f1419] leading-relaxed">
        <h3 class="font-bold text-lg text-[#0f1419] pb-3 border-b border-[#eff3f4]">Langkah Penutupan Event & Rekonsiliasi Kas</h3>
        
        <div class="space-y-4 text-xs text-[#536471]">
            <div class="flex gap-3">
                <span class="font-bold text-[#1d9bf0] shrink-0">Langkah 1:</span>
                <p>Pastikan seluruh transaksi berstatus <strong>Pending Verification</strong> sudah selesai diproses (disetujui atau ditolak).</p>
            </div>
            <div class="flex gap-3">
                <span class="font-bold text-[#1d9bf0] shrink-0">Langkah 2:</span>
                <p>Buka menu <strong>Laporan Full</strong>, cetak atau ekspor PDF rekapitulasi penjualan seluruh warung.</p>
            </div>
            <div class="flex gap-3">
                <span class="font-bold text-[#1d9bf0] shrink-0">Langkah 3:</span>
                <p>Lakukan transfer rekonsiliasi hak 75% hasil penjualan QRIS kepada masing-masing rekening pemilik warung.</p>
            </div>
        </div>
    </div>
</div>
@endsection
