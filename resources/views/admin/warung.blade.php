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

        <div class="text-xs font-black text-[#0f1419] bg-white px-4 py-2 rounded-full border border-[#eff3f4] shadow-xs">
            Total Stand Aktif: <span class="text-[#1d9bf0] font-black" x-text="$store.app.stores.length"></span>
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

    <!-- Stores Grid (Twitter UI) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <template x-for="store in filteredStores" :key="store.id">
            <div class="bg-white rounded-3xl border border-[#eff3f4] p-5 shadow-xs hover:border-[#1d9bf0]/40 transition-all flex flex-col justify-between space-y-4">
                <div>
                    <!-- Top Info -->
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-0.5 rounded-full bg-[#f7f9f9] font-mono text-[10px] font-bold text-[#0f1419] border border-[#eff3f4]" x-text="store.booth_number || 'Stand A-01'"></span>
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
                <div class="pt-2 border-t border-[#eff3f4] flex items-center justify-between gap-2">
                    <!-- WhatsApp Link -->
                    <a 
                        :href="`https://wa.me/${store.phone ? store.phone.replace(/^0/, '62') : '6281234567890'}`" 
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[#e8f5fd] hover:bg-[#1d9bf0] text-[#1d9bf0] hover:text-white text-xs font-black transition-colors border border-[#bde2f9] cursor-pointer"
                    >
                        <span>💬 Chat WA</span>
                    </a>

                    <!-- Detail Menu Modal Trigger (Twitter Blue Pill) -->
                    <button 
                        @click="openDetail(store)"
                        type="button" 
                        class="px-4 py-2 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs font-black shadow-xs transition-colors cursor-pointer"
                    >
                        Lihat Menu Stand &rarr;
                    </button>
                </div>
            </div>
        </template>
    </div>

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

                <div class="pt-2 text-right">
                    <button @click="storeDetailModalOpen = false" class="w-full sm:w-auto px-6 py-2.5 bg-[#1d9bf0] text-white text-xs font-black rounded-full cursor-pointer hover:bg-[#1a8cd8] shadow-md shadow-[#1d9bf0]/25 transition-all">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
