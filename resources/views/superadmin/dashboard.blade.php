@extends('layouts.app')

@section('title', 'Dashboard Super Admin Multi-Event')

@section('content')
<div x-data class="space-y-6">

    <!-- Header Section (Twitter UI) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3.5 py-1 rounded-full bg-[#e8f5fd] text-[#1d9bf0] text-xs font-black uppercase border border-[#bde2f9]">Super Admin Multi-Event</span>
                <span class="text-xs text-[#0f1419] font-semibold">Lisensi Sistem POS UMKM</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-[#0f1419] tracking-tight mt-1.5 flex items-center gap-1.5">
                <span>Platform Owner & Multi-Tenant Control</span>
                <svg class="w-5 h-5 text-[#1d9bf0] shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.79-4-4-4-.495 0-.965.084-1.4.238C14.55 2.475 13.18 1.6 11.6 1.6c-1.58 0-2.95.875-3.6 2.148-.435-.154-.905-.238-1.4-.238-2.21 0-4 1.79-4 4 0 .495.084.965.238 1.4C1.575 9.55.7 10.92.7 12.5c0 1.58.875 2.95 2.148 3.6-.154.435-.238.905-.238 1.4 0 2.21 1.79 4 4 4 .495 0 .965-.084 1.4-.238.65 1.273 2.02 2.148 3.6 2.148 1.58 0 2.95-.875 3.6-2.148.435.154.905.238 1.4.238 2.21 0 4-1.79 4-4 0-.495-.084-.965-.238-1.4 1.273-.65 2.148-2.02 2.148-3.6zm-12.28 4.22l-4.22-4.22 1.414-1.414 2.806 2.806 6.806-6.806 1.414 1.414-8.22 8.22z"></path></svg>
            </h2>
            <p class="text-xs sm:text-sm text-[#0f1419] font-medium mt-0.5">Pengawasan omzet gross platform dan akumulasi royalti flat fee Rp1.000 per transaksi paid</p>
        </div>

        <a 
            href="/superadmin/events" 
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs sm:text-sm font-black shadow-md shadow-[#1d9bf0]/25 transition-all cursor-pointer active:scale-95"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            <span>Kelola Multi-Event</span>
        </a>
    </div>

    <!-- KPI Metric Cards (Twitter Blue Accents & Crisp Black Font) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Platform Fee -->
        <div class="bg-gradient-to-br from-[#1d9bf0] to-[#1271b3] rounded-3xl p-5 text-white shadow-lg shadow-[#1d9bf0]/25">
            <span class="text-xs font-bold text-white/90 uppercase tracking-wider block">Total Royalti Lisensi</span>
            <h3 class="text-2xl sm:text-3xl font-black mt-2 tracking-tight text-white" x-text="formatRupiah($store.app.transactions.filter(t => t.status === 'paid').length * 1000)"></h3>
            <p class="text-xs text-white/90 mt-2 font-medium">Rp1.000 × <span class="font-black text-white" x-text="$store.app.transactions.filter(t => t.status === 'paid').length"></span> transaksi paid</p>
        </div>

        <!-- 2. Gross Volume Platform -->
        <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs flex flex-col justify-between">
            <div>
                <span class="text-xs font-bold text-[#0f1419] uppercase tracking-wider block">Gross Volume Platform</span>
                <h3 class="text-xl font-black text-[#0f1419] mt-2" x-text="formatRupiah($store.app.transactions.filter(t => t.status === 'paid').reduce((sum, t) => sum + t.total_amount, 0))"></h3>
            </div>
            <p class="text-xs text-[#536471] mt-3 font-semibold">Total nilai perputaran uang</p>
        </div>

        <!-- 3. Total Events -->
        <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs flex flex-col justify-between">
            <div>
                <span class="text-xs font-bold text-[#0f1419] uppercase tracking-wider block">Total Event Terdaftar</span>
                <h3 class="text-xl font-black text-[#1d9bf0] mt-2" x-text="`${$store.app.events.length} Event`"></h3>
            </div>
            <p class="text-xs text-[#536471] mt-3 font-semibold">1 event aktif saat ini</p>
        </div>

        <!-- 4. Total Tenants Across Platform -->
        <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs flex flex-col justify-between">
            <div>
                <span class="text-xs font-bold text-[#0f1419] uppercase tracking-wider block">Total Tenant Stand</span>
                <h3 class="text-xl font-black text-[#0f1419] mt-2" x-text="`${$store.app.stores.length} Stand Warung`"></h3>
            </div>
            <p class="text-xs text-[#536471] mt-3 font-semibold">Tersebar di berbagai event</p>
        </div>
    </div>

    <!-- Active Event Banner (Twitter UI) -->
    <div class="bg-white rounded-3xl p-6 border-2 border-[#1d9bf0] shadow-sm relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-0.5 rounded-full bg-[#e8f5fd] text-[#1d9bf0] text-xs font-black uppercase border border-[#bde2f9]">
                        ● Event Berjalan
                    </span>
                    <span class="text-xs text-[#536471] font-mono" x-text="`#ID: ${$store.app.getActiveEvent()?.id}`"></span>
                </div>
                <h3 class="text-lg sm:text-xl font-black text-[#0f1419] mt-1.5" x-text="$store.app.getActiveEvent()?.name"></h3>
                <p class="text-xs text-[#0f1419] mt-1">
                    📅 <span class="font-bold" x-text="`${$store.app.getActiveEvent()?.start_date} s/d ${$store.app.getActiveEvent()?.end_date}`"></span>
                    • 📍 <span class="font-bold" x-text="$store.app.getActiveEvent()?.location || 'Area Bazar'"></span>
                </p>
            </div>

            <a 
                href="/admin/dashboard" 
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs font-black transition-colors cursor-pointer shadow-xs"
            >
                Masuk ke Panel EO &rarr;
            </a>
        </div>
    </div>
</div>
@endsection
