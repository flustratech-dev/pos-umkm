@extends('layouts.app')

@section('title', 'Semua Produk Warung Tenant')

@section('content')
<div x-data="{
    searchQuery: '',
    selectedStoreId: 'all',
    selectedCategory: 'all',

    get allProducts() {
        return $store.app.products.filter(p => {
            const matchesSearch = p.title.toLowerCase().includes(this.searchQuery.toLowerCase());
            const matchesStore = this.selectedStoreId === 'all' || p.store_id === parseInt(this.selectedStoreId);
            const matchesCategory = this.selectedCategory === 'all' || p.category === this.selectedCategory;
            return matchesSearch && matchesStore && matchesCategory;
        });
    },

    getStoreName(storeId) {
        const s = $store.app.stores.find(x => x.id === storeId);
        return s ? s.name : 'Warung UMKM';
    }
}" class="space-y-6">

    <!-- Header (Twitter UI) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3.5 py-0.5 rounded-full bg-[#e8f5fd] text-[#1d9bf0] text-[10px] font-black uppercase border border-[#bde2f9]">Katalog Lintas Stand</span>
                <span class="text-xs text-[#0f1419] font-semibold">Read-Only View</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-[#0f1419] tracking-tight mt-1">Daftar Produk Semua Warung</h2>
            <p class="text-xs sm:text-sm text-[#0f1419] font-medium mt-0.5">Monitoring menu dan harga jual seluruh tenant di event aktif</p>
        </div>

        <div class="text-xs font-black text-[#0f1419] bg-white px-4 py-2 rounded-full border border-[#eff3f4] shadow-xs">
            Total Menu Terdaftar: <span class="text-[#1d9bf0] font-black" x-text="$store.app.products.length"></span>
        </div>
    </div>

    <!-- Filters & Search (Twitter UI) -->
    <div class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between bg-white p-3.5 rounded-2xl border border-[#eff3f4] shadow-xs">
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#536471]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input 
                type="text" 
                x-model="searchQuery"
                placeholder="Cari nama menu..." 
                class="w-full pl-9 pr-4 py-2 bg-[#f7f9f9] border border-[#eff3f4] rounded-full text-xs sm:text-sm text-[#0f1419] placeholder-[#536471] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none font-semibold"
            >
        </div>

        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
            <!-- Filter Warung -->
            <select 
                x-model="selectedStoreId" 
                class="px-4 py-2 bg-[#f7f9f9] border border-[#eff3f4] rounded-full text-xs font-black text-[#0f1419] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none cursor-pointer"
            >
                <option value="all">Semua Warung</option>
                <template x-for="store in $store.app.stores" :key="store.id">
                    <option :value="store.id" x-text="store.name"></option>
                </template>
            </select>

            <!-- Filter Kategori -->
            <select 
                x-model="selectedCategory" 
                class="px-4 py-2 bg-[#f7f9f9] border border-[#eff3f4] rounded-full text-xs font-black text-[#0f1419] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none cursor-pointer"
            >
                <option value="all">Semua Kategori</option>
                <option value="Makanan">Makanan</option>
                <option value="Minuman">Minuman</option>
                <option value="Snack">Snack</option>
            </select>
        </div>
    </div>

    <!-- Product Grid / Mobile Horizontal Cards (Kanan-Kiri, Twitter UI) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-4">
        <template x-for="product in allProducts" :key="product.id">
            <div class="bg-white rounded-2xl sm:rounded-3xl border border-[#eff3f4] overflow-hidden shadow-xs hover:shadow-md transition-all flex flex-row sm:flex-col justify-between group p-3 sm:p-0 gap-3 sm:gap-0">
                <!-- Thumbnail (Kiri di Mobile, Atas di Desktop) -->
                <div class="relative w-24 h-24 sm:w-full sm:h-40 bg-[#f7f9f9] rounded-xl sm:rounded-none overflow-hidden shrink-0">
                    <img :src="product.photo" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <span class="hidden sm:inline-block absolute top-3 left-3 px-3 py-1 rounded-full text-[10px] font-black bg-[#1d9bf0] text-white shadow-xs" x-text="product.category"></span>
                </div>

                <!-- Product Details (Kanan di Mobile, Bawah di Desktop) -->
                <div class="flex-1 min-w-0 flex flex-col justify-between sm:p-4">
                    <div>
                        <div class="flex items-center gap-1.5 mb-1 sm:mb-0">
                            <span class="text-[10px] font-black text-[#1d9bf0] uppercase tracking-wider truncate" x-text="getStoreName(product.store_id)"></span>
                            <span class="sm:hidden text-[9px] px-2 py-0.5 rounded-full bg-[#f7f9f9] text-[#0f1419] font-bold border border-[#eff3f4]" x-text="product.category"></span>
                        </div>
                        <h4 class="font-black text-[#0f1419] text-xs sm:text-sm mt-0.5 line-clamp-1" x-text="product.title"></h4>
                        <p class="text-[11px] sm:text-xs text-[#536471] mt-0.5 line-clamp-1 sm:line-clamp-2 font-medium" x-text="product.description || 'Menu pilihan stand event'"></p>
                    </div>

                    <div class="pt-2 sm:pt-4 sm:mt-2 sm:border-t sm:border-[#eff3f4] flex items-center justify-between">
                        <span class="text-[10px] text-[#536471] font-semibold hidden sm:inline">Harga:</span>
                        <span class="text-xs sm:text-sm font-black text-[#0f1419]" x-text="formatRupiah(product.price)"></span>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
@endsection
