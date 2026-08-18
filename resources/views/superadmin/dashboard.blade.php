@extends('layouts.app')

@section('title', 'Dashboard Developer Multi-Event')

@section('content')
<div x-data class="space-y-6">

    <!-- Header Section (Twitter UI) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3.5 py-1 rounded-full bg-[#e8f5fd] text-[#1d9bf0] text-xs font-black uppercase border border-[#bde2f9]">Developer Multi-Event</span>
                <span class="text-xs text-[#0f1419] font-semibold">Lisensi Sistem JADISATU</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-[#0f1419] tracking-tight mt-1.5 flex items-center gap-1.5">
                <span>Platform Owner & Multi-Tenant Control</span>
                <svg class="w-5 h-5 text-[#1d9bf0] shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.79-4-4-4-.495 0-.965.084-1.4.238C14.55 2.475 13.18 1.6 11.6 1.6c-1.58 0-2.95.875-3.6 2.148-.435-.154-.905-.238-1.4-.238-2.21 0-4 1.79-4 4 0 .495.084.965.238 1.4C1.575 9.55.7 10.92.7 12.5c0 1.58.875 2.95 2.148 3.6-.154.435-.238.905-.238 1.4 0 2.21 1.79 4 4 4 .495 0 .965-.084 1.4-.238.65 1.273 2.02 2.148 3.6 2.148 1.58 0 2.95-.875 3.6-2.148.435.154.905.238 1.4.238 2.21 0 4-1.79 4-4 0-.495-.084-.965-.238-1.4 1.273-.65 2.148-2.02 2.148-3.6zm-12.28 4.22l-4.22-4.22 1.414-1.414 2.806 2.806 6.806-6.806 1.414 1.414-8.22 8.22z"></path></svg>
            </h2>
            <p class="text-xs sm:text-sm text-[#0f1419] font-medium mt-0.5">Pengawasan omzet gross platform dan akumulasi fee developer Rp1.000 per transaksi paid</p>
        </div>

        <a 
            href="/superadmin/events" 
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs sm:text-sm font-black shadow-md shadow-[#1d9bf0]/25 transition-all cursor-pointer active:scale-95"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            <span>Kelola Multi-Event</span>
        </a>
    </div>

    <!-- KENDALI MASA TESTING / SIMULASI (SuperAdmin Exclusive) -->
    <template x-if="$store.app.getActiveEvent()">
        <div class="bg-white rounded-3xl p-4 sm:p-5 border border-[#eff3f4] shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center gap-3.5">
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 transition-colors"
                     :class="$store.app.getActiveEvent()?.is_testing_mode ? 'bg-amber-50 text-amber-600 border border-amber-200' : 'bg-[#f7f9f9] text-[#536471] border border-[#eff3f4]'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h4 class="text-sm font-black text-[#0f1419]">Masa Testing / Uji Coba Transaksi: <span class="text-[#1d9bf0]" x-text="$store.app.getActiveEvent()?.name"></span></h4>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black"
                              :class="$store.app.getActiveEvent()?.is_testing_mode ? 'bg-amber-100 text-amber-700 border border-amber-300' : 'bg-[#f7f9f9] text-[#536471] border border-[#eff3f4]'"
                              x-text="$store.app.getActiveEvent()?.is_testing_mode ? 'AKTIF (Uji Coba)' : 'NONAKTIF (Riil)'">
                        </span>
                    </div>
                    <p class="text-xs text-[#536471] font-semibold mt-0.5">
                        Kontrol simulasi kasir & sosialisasi tenant. Saat selesai, data transaksi testing dapat dibersihkan sekali klik tanpa menghapus tenant/produk.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0 self-end sm:self-center">
                <!-- Toggle Switch -->
                <button 
                    @click="$store.app.toggleEventTesting($store.app.getActiveEvent()?.id)"
                    type="button"
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                    :class="$store.app.getActiveEvent()?.is_testing_mode ? 'bg-amber-500' : 'bg-gray-200'"
                    role="switch"
                    :aria-checked="$store.app.getActiveEvent()?.is_testing_mode"
                    title="Aktifkan/Nonaktifkan Masa Testing"
                >
                    <span 
                        aria-hidden="true" 
                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"
                        :class="$store.app.getActiveEvent()?.is_testing_mode ? 'translate-x-5' : 'translate-x-0'"
                    ></span>
                </button>

                <!-- Tombol Hapus Transaksi Testing (Reset) -->
                <button 
                    @click="$store.app.openResetTestingModal($store.app.getActiveEvent())"
                    type="button"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-[#f4212e]/10 hover:bg-[#f4212e] text-[#f4212e] hover:text-white text-xs font-black transition-all cursor-pointer border border-[#f4212e]/20 active:scale-95 shadow-2xs"
                    title="Bersihkan semua transaksi yang dilakukan selama masa testing"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <span>Reset Transaksi Testing</span>
                </button>
            </div>
        </div>
    </template>

    <!-- 1 Card Menu dengan 4 Kotak Icon (Mobile Only - Tepat di bawah Header) -->
    <div class="lg:hidden bg-white rounded-3xl p-4 sm:p-5 border border-[#eff3f4] shadow-xs">
        <div class="grid grid-cols-4 gap-2 sm:gap-4 text-center">
            <!-- 1. Multi-Event -->
            <a 
                href="/superadmin/events" 
                class="flex flex-col items-center group cursor-pointer active:scale-95 transition-transform"
            >
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-[#e8f5fd] group-hover:bg-[#1d9bf0] text-[#1d9bf0] group-hover:text-white flex items-center justify-center transition-all shadow-2xs group-hover:shadow-md group-hover:shadow-[#1d9bf0]/25">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <span class="text-[11px] sm:text-xs font-black text-[#0f1419] group-hover:text-[#1d9bf0] mt-2 block tracking-tight truncate w-full">Event</span>
            </a>

            <!-- 2. Stand Warung -->
            <a 
                href="/superadmin/warung" 
                class="flex flex-col items-center group cursor-pointer active:scale-95 transition-transform"
            >
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-[#e8f5fd] group-hover:bg-[#1d9bf0] text-[#1d9bf0] group-hover:text-white flex items-center justify-center transition-all shadow-2xs group-hover:shadow-md group-hover:shadow-[#1d9bf0]/25">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <span class="text-[11px] sm:text-xs font-black text-[#0f1419] group-hover:text-[#1d9bf0] mt-2 block tracking-tight truncate w-full">Warung</span>
            </a>

            <!-- 3. Helpdesk -->
            <a 
                href="/superadmin/helpdesk" 
                class="flex flex-col items-center group cursor-pointer active:scale-95 transition-transform"
            >
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-[#e8f5fd] group-hover:bg-[#1d9bf0] text-[#1d9bf0] group-hover:text-white flex items-center justify-center transition-all shadow-2xs group-hover:shadow-md group-hover:shadow-[#1d9bf0]/25">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                </div>
                <span class="text-[11px] sm:text-xs font-black text-[#0f1419] group-hover:text-[#1d9bf0] mt-2 block tracking-tight truncate w-full">Helpdesk</span>
            </a>

            <!-- 4. SOP / Panduan -->
            <a 
                href="/superadmin/panduan" 
                class="flex flex-col items-center group cursor-pointer active:scale-95 transition-transform"
            >
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-[#e8f5fd] group-hover:bg-[#1d9bf0] text-[#1d9bf0] group-hover:text-white flex items-center justify-center transition-all shadow-2xs group-hover:shadow-md group-hover:shadow-[#1d9bf0]/25">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <span class="text-[11px] sm:text-xs font-black text-[#0f1419] group-hover:text-[#1d9bf0] mt-2 block tracking-tight truncate w-full">SOP Kasir</span>
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards (Twitter Blue Accents & Crisp Black Font) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Platform Fee -->
        <div class="bg-gradient-to-br from-[#1d9bf0] to-[#1271b3] rounded-3xl p-5 text-white shadow-lg shadow-[#1d9bf0]/25">
            <span class="text-xs font-bold text-white/90 uppercase tracking-wider block">Total Fee Developer</span>
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
@endsection
