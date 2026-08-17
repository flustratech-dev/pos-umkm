@extends('layouts.app')

@section('title', 'Daftar Warung & Tenant Event')

@section('content')
<div x-data="{
    searchStore: '',
    selectedStoreDetail: null,
    storeDetailModalOpen: false,

    get filteredStores() {
        return $store.app.stores.filter(s => {
            return s.name.toLowerCase().includes(this.searchStore.toLowerCase()) || 
                   s.owner_name.toLowerCase().includes(this.searchStore.toLowerCase());
        });
    },

    getStoreProducts(storeId) {
        return $store.app.products.filter(p => p.store_id === storeId);
    },

    getStoreRevenue(storeId) {
        const paid = $store.app.transactions.filter(t => t.store_id === storeId && t.status === 'paid');
        return paid.reduce((sum, t) => sum + t.total_amount, 0);
    },

    pullModalOpen: false,

    openDetail(store) {
        this.selectedStoreDetail = store;
        this.storeDetailModalOpen = true;
    }
}" class="space-y-6">

    <!-- Header (Twitter UI) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3.5 py-0.5 rounded-full bg-[#e8f5fd] text-[#1d9bf0] text-[10px] font-black uppercase border border-[#bde2f9]">Direktori Tenant</span>
                <span class="text-xs text-[#0f1419] font-semibold">Stand Bazar UMKM</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-[#0f1419] tracking-tight mt-1">Daftar Warung & Pemilik</h2>
            <p class="text-xs sm:text-sm text-[#0f1419] font-medium mt-0.5">Kelola data stand, kontak WhatsApp pemilik, dan pantau penjualan per stand</p>
        </div>

        <div class="flex items-center gap-3">
            <div class="text-xs font-black text-[#0f1419] bg-white px-4 py-2 rounded-full border border-[#eff3f4] shadow-xs">
                Total Stand Aktif: <span class="text-[#1d9bf0] font-black" x-text="$store.app.stores.length"></span>
            </div>
            <button @click="pullModalOpen = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#0f1419] hover:bg-[#272c30] text-white text-xs font-black transition-colors shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                Tarik Tenant Event Lama
            </button>
        </div>
    </div>

    <!-- Search Input (Twitter UI) -->
    <div class="bg-white p-3.5 rounded-2xl border border-[#eff3f4] shadow-xs max-w-md">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#536471]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input 
                type="text" 
                x-model="searchStore"
                placeholder="Cari nama warung atau nama pemilik..." 
                class="w-full pl-9 pr-4 py-2 bg-[#f7f9f9] border border-[#eff3f4] rounded-full text-xs sm:text-sm text-[#0f1419] placeholder-[#536471] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none font-semibold"
            >
        </div>
    </div>

    <!-- PULL STORE MODAL -->
    <div x-show="pullModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-[#0f1419]/60 backdrop-blur-sm transition-opacity" @click="pullModalOpen = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative max-w-lg w-full bg-white rounded-3xl p-6 shadow-2xl border border-[#eff3f4] overflow-hidden">
                <div class="flex items-center justify-between mb-4 pb-4 border-b border-[#eff3f4]">
                    <div>
                        <h3 class="text-lg font-black text-[#0f1419]">Tarik Tenant Event Lama</h3>
                        <p class="text-[11px] text-[#536471] font-semibold mt-0.5">Pilih tenant dari event sebelumnya untuk diikutsertakan ke event ini. Produk akan disalin otomatis.</p>
                    </div>
                    <button @click="pullModalOpen = false" class="p-1.5 rounded-full hover:bg-[#eff3f4] text-[#0f1419]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <form action="{{ route('admin.warung.pull') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-[#0f1419] mb-1.5">Pilih Tenant / Warung</label>
                            <select name="old_store_id" required class="w-full px-4 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-xs font-semibold focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none cursor-pointer">
                                <option value="" disabled selected>-- Pilih tenant dari event lama --</option>
                                @foreach($inactiveStores as $is)
                                    <option value="{{ $is->id }}">{{ $is->name }} (Pemilik: {{ $is->owner->name ?? '-' }}) - dari event: {{ $is->event->name ?? '-' }}</option>
                                @endforeach
                            </select>
                            @if($inactiveStores->isEmpty())
                                <p class="text-[11px] text-[#f4212e] font-medium mt-1">Tidak ada tenant dari event lama yang bisa ditarik, atau semua sudah terdaftar.</p>
                            @endif
                        </div>
                        
                        <div class="pt-2">
                            <button type="submit" @if($inactiveStores->isEmpty()) disabled @endif class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] disabled:opacity-50 text-white text-sm font-black transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                Tarik Tenant & Produk Sekarang
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Stores Grid (Twitter UI) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <template x-for="store in filteredStores" :key="store.id">
            <div class="bg-white rounded-3xl border border-[#eff3f4] p-5 shadow-xs hover:border-[#1d9bf0]/40 transition-all flex flex-col justify-between space-y-4">
                <div>
                    <!-- Top Info -->
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-0.5 rounded-full bg-[#f7f9f9] font-mono text-[10px] font-bold text-[#0f1419] border border-[#eff3f4]" x-text="store.booth_number || 'Stand 01'"></span>
                        <span class="px-3 py-0.5 rounded-full text-[10px] font-black bg-[#e8f5fd] text-[#1d9bf0] border border-[#bde2f9]">
                            Aktif Jualan
                        </span>
                    </div>

                    <h3 class="font-black text-base text-[#0f1419] mt-2" x-text="store.name"></h3>
                    <p class="text-xs text-[#536471] font-semibold" x-text="`Pemilik: ${store.owner_name}`"></p>
                    <span class="inline-block mt-1 text-[11px] px-2.5 py-0.5 rounded-full bg-[#f7f9f9] text-[#0f1419] font-bold border border-[#eff3f4]" x-text="store.category || 'Makanan & Minuman'"></span>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 gap-2 text-xs p-3.5 bg-[#f7f9f9] rounded-2xl border border-[#eff3f4]">
                    <div>
                        <span class="text-[10px] text-[#536471] block font-semibold">Total Menu</span>
                        <span class="font-black text-[#0f1419]" x-text="getStoreProducts(store.id).length + ' Menu'"></span>
                    </div>
                    <div>
                        <span class="text-[10px] text-[#536471] block font-semibold">Omzet Terkumpul</span>
                        <span class="font-black text-[#1d9bf0]" x-text="formatRupiah(getStoreRevenue(store.id))"></span>
                    </div>
                </div>

                <!-- Action Links (Twitter Style Pills) -->
                <div class="pt-2 border-t border-[#eff3f4] flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <!-- WhatsApp Link -->
                        <a 
                            :href="`https://wa.me/${store.phone ? store.phone.replace(/^0/, '62') : '6281234567890'}`" 
                            target="_blank"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-[#e8f5fd] hover:bg-[#1d9bf0] text-[#1d9bf0] hover:text-white text-xs font-black transition-colors border border-[#bde2f9] cursor-pointer"
                        >
                            <span>💬 Chat WA</span>
                        </a>

                        <!-- PDF Laporan Button -->
                        <button 
                            @click="$store.app.printTenantReport(store.id)" 
                            type="button"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-[#0f1419] hover:bg-[#272c30] text-white text-xs font-black transition-colors shadow-2xs cursor-pointer"
                            title="Cetak PDF / Dokumen Stand Ini"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            <span>PDF</span>
                        </button>

                        <!-- Impersonate Button (Masuk sebagai Stand) -->
                        <form :action="`{{ (auth()->user() && auth()->user()->isSuperAdmin()) ? '/superadmin/impersonate/' : '/admin/impersonate/' }}${store.id}`" method="POST" class="inline">
                            @csrf
                            <button 
                                type="submit" 
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-[#0f1419] hover:bg-[#272c30] text-white text-xs font-black transition-all cursor-pointer shadow-xs active:scale-95"
                                title="Buka terminal kasir dan kelola menu langsung sebagai warung ini"
                            >
                                <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Inspeksi Kasir</span>
                            </button>
                        </form>
                    </div>

                    <!-- Detail Menu Modal Trigger (Twitter Blue Pill) -->
                    <button 
                        @click="openDetail(store)" 
                        type="button" 
                        class="px-4 py-1.5 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs font-black shadow-xs transition-colors cursor-pointer"
                    >
                        Lihat Menu Stand &rarr;
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <template x-if="filteredStores.length === 0">
        <div class="bg-white rounded-3xl border border-[#eff3f4] p-12 text-center max-w-md mx-auto my-8 shadow-2xs">
            <div class="w-16 h-16 bg-[#e8f5fd] rounded-full text-[#1d9bf0] flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <h4 class="text-sm font-black text-[#0f1419]">Belum Ada Warung / Stand Terdaftar</h4>
            <p class="text-xs text-[#536471] font-semibold mt-1">Tenant baru yang mendaftar secara mandiri melalui form registrasi akan langsung muncul di sini.</p>
        </div>
    </template>

    <!-- STORE DETAIL MODAL (SLIDE UP BOTTOM SHEET ON MOBILE) -->
    <div 
        x-show="storeDetailModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <!-- Backdrop -->
        <div 
            x-show="storeDetailModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-[#0f1419]/60 backdrop-blur-xs transition-opacity" 
            @click="storeDetailModalOpen = false"
        ></div>

        <!-- Position: Bottom on Mobile (`items-end p-0`), Center on Desktop (`sm:items-center sm:p-4`) -->
        <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4 text-left">
            <div 
                x-show="storeDetailModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                class="relative w-full max-w-lg bg-white rounded-t-3xl sm:rounded-3xl p-6 sm:p-8 shadow-2xl space-y-4 border-t sm:border border-[#eff3f4] text-left max-h-[92vh] sm:max-h-none overflow-y-auto custom-scrollbar"
            >
                <div class="w-12 h-1.5 bg-[#cfd9de] rounded-full mx-auto mb-2 sm:hidden"></div>

                <div class="flex items-center justify-between pb-3 border-b border-[#eff3f4]">
                    <div>
                        <h3 class="text-base font-black text-[#0f1419]" x-text="selectedStoreDetail?.name"></h3>
                        <p class="text-xs text-[#536471] font-medium" x-text="`Pemilik: ${selectedStoreDetail?.owner_name} • ${selectedStoreDetail?.phone}`"></p>
                    </div>
                    <button @click="storeDetailModalOpen = false" class="text-[#0f1419] hover:text-[#1d9bf0] p-1.5 rounded-full hover:bg-[#eff3f4] cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="space-y-2.5 max-h-72 overflow-y-auto custom-scrollbar">
                    <template x-for="p in getStoreProducts(selectedStoreDetail?.id)" :key="p.id">
                        <div class="p-3 bg-[#f7f9f9] rounded-2xl flex items-center justify-between gap-3 border border-[#eff3f4]">
                            <img :src="p.photo" class="w-12 h-12 rounded-xl object-cover border border-[#eff3f4]">
                            <div class="flex-1 min-w-0">
                                <h5 class="font-black text-xs text-[#0f1419] truncate" x-text="p.title"></h5>
                                <span class="text-[10px] text-[#536471] font-semibold" x-text="p.category"></span>
                            </div>
                            <span class="text-xs font-black text-[#1d9bf0]" x-text="formatRupiah(p.price)"></span>
                        </div>
                    </template>
                </div>

                <div class="pt-3 border-t border-[#eff3f4] flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2">
                    <form :action="selectedStoreDetail ? `{{ (auth()->user() && auth()->user()->isSuperAdmin()) ? '/superadmin/impersonate/' : '/admin/impersonate/' }}${selectedStoreDetail.id}` : '#'" method="POST">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-[#0f1419] hover:bg-[#272c30] text-white text-xs font-black rounded-full cursor-pointer shadow-xs transition-all flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Masuk & Kelola sebagai Stand Ini</span>
                        </button>
                    </form>
                    <button @click="storeDetailModalOpen = false" class="w-full sm:w-auto px-6 py-2.5 bg-[#eff3f4] text-[#0f1419] text-xs font-black rounded-full cursor-pointer hover:bg-slate-200 transition-all">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
