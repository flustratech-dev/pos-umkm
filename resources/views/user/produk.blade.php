@extends('layouts.app')

@section('title', 'Kelola Menu Produk')
@section('page_title', 'Kelola Menu Produk')

@section('content')
<div x-data="{
    search: '',
    selectedCategory: 'all',
    
    get filteredProducts() {
        const storeId = $store.app.getCurrentStore()?.id;
        return $store.app.products.filter(p => {
            const matchesStore = storeId ? p.store_id === storeId : true;
            const matchSearch = p.title.toLowerCase().includes(this.search.toLowerCase());
            const matchCategory = this.selectedCategory === 'all' || p.category === this.selectedCategory;
            return matchesStore && matchSearch && matchCategory;
        });
    }
}">
    <!-- Header Banner & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-[#0f1419] tracking-tight">Daftar Menu Stand</h2>
            <p class="text-xs sm:text-sm text-[#536471] font-semibold mt-0.5">Kelola makanan, minuman, dan harga jual yang tampil di kasir</p>
        </div>

        <!-- Tambah Menu Button (Twitter Blue Pill) -->
        <button 
            @click="$store.app.openAddProductModal()"
            class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs sm:text-sm font-black shadow-md shadow-[#1d9bf0]/25 transition-all active:scale-95 shrink-0 cursor-pointer"
        >
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            <span>Tambah Menu Baru</span>
        </button>
    </div>

    <!-- Search & Category Filters -->
    <div class="flex flex-col md:flex-row gap-3 mb-6 items-stretch md:items-center justify-between">
        <!-- Search Input -->
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#536471]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input 
                type="text" 
                x-model="search" 
                placeholder="Cari nama menu atau varian..." 
                class="w-full pl-10 pr-4 py-2.5 bg-white border border-[#eff3f4] rounded-full text-xs sm:text-sm text-[#0f1419] placeholder-[#536471] focus:ring-2 focus:ring-[#1d9bf0] focus:bg-white transition-all font-semibold shadow-2xs"
            >
        </div>

        <!-- Mobile Layout (Semua 1 Lebar, 3 Berjejer) -->
        <div class="flex flex-col gap-2 md:hidden">
            <button 
                @click="selectedCategory = 'all'"
                class="w-full py-2.5 px-4 rounded-2xl text-xs font-black transition-all text-center cursor-pointer shadow-2xs"
                :class="selectedCategory === 'all' ? 'bg-[#1d9bf0] text-white shadow-xs' : 'bg-white hover:bg-[#eff3f4] text-[#0f1419] border border-[#eff3f4]'"
            >
                ✨ Semua Produk
            </button>
            <div class="grid grid-cols-3 gap-2">
                <button 
                    @click="selectedCategory = 'Makanan'"
                    class="py-2.5 px-2 rounded-2xl text-xs font-black transition-all text-center cursor-pointer truncate shadow-2xs"
                    :class="selectedCategory === 'Makanan' ? 'bg-[#1d9bf0] text-white shadow-xs' : 'bg-white hover:bg-[#eff3f4] text-[#0f1419] border border-[#eff3f4]'"
                >
                    🍱 Makanan
                </button>
                <button 
                    @click="selectedCategory = 'Minuman'"
                    class="py-2.5 px-2 rounded-2xl text-xs font-black transition-all text-center cursor-pointer truncate shadow-2xs"
                    :class="selectedCategory === 'Minuman' ? 'bg-[#1d9bf0] text-white shadow-xs' : 'bg-white hover:bg-[#eff3f4] text-[#0f1419] border border-[#eff3f4]'"
                >
                    🧋 Minuman
                </button>
                <button 
                    @click="selectedCategory = 'Snack'"
                    class="py-2.5 px-2 rounded-2xl text-xs font-black transition-all text-center cursor-pointer truncate shadow-2xs"
                    :class="selectedCategory === 'Snack' ? 'bg-[#1d9bf0] text-white shadow-xs' : 'bg-white hover:bg-[#eff3f4] text-[#0f1419] border border-[#eff3f4]'"
                >
                    🍟 Snack
                </button>
            </div>
        </div>

        <!-- Desktop Layout (Baris Sejajar Asli) -->
        <div class="hidden md:flex items-center gap-1.5 shrink-0">
            <button 
                @click="selectedCategory = 'all'"
                class="px-4 py-2 rounded-full text-xs font-black transition-all shrink-0 cursor-pointer"
                :class="selectedCategory === 'all' ? 'bg-[#1d9bf0] text-white shadow-2xs' : 'bg-white hover:bg-[#eff3f4] text-[#0f1419] border border-[#eff3f4]'"
            >
                Semua Kategori
            </button>
            <button 
                @click="selectedCategory = 'Makanan'"
                class="px-4 py-2 rounded-full text-xs font-black transition-all shrink-0 cursor-pointer"
                :class="selectedCategory === 'Makanan' ? 'bg-[#1d9bf0] text-white shadow-2xs' : 'bg-white hover:bg-[#eff3f4] text-[#0f1419] border border-[#eff3f4]'"
            >
                🍱 Makanan
            </button>
            <button 
                @click="selectedCategory = 'Minuman'"
                class="px-4 py-2 rounded-full text-xs font-black transition-all shrink-0 cursor-pointer"
                :class="selectedCategory === 'Minuman' ? 'bg-[#1d9bf0] text-white shadow-2xs' : 'bg-white hover:bg-[#eff3f4] text-[#0f1419] border border-[#eff3f4]'"
            >
                🧋 Minuman
            </button>
            <button 
                @click="selectedCategory = 'Snack'"
                class="px-4 py-2 rounded-full text-xs font-black transition-all shrink-0 cursor-pointer"
                :class="selectedCategory === 'Snack' ? 'bg-[#1d9bf0] text-white shadow-2xs' : 'bg-white hover:bg-[#eff3f4] text-[#0f1419] border border-[#eff3f4]'"
            >
                🍟 Snack
            </button>
        </div>
    </div>

    <!-- Products Grid (2 Cards side-by-side on Mobile, Compact Grid on Desktop) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 gap-2.5 sm:gap-3.5">
        <template x-for="product in filteredProducts" :key="product.id">
            <div class="bg-white rounded-2xl border border-[#eff3f4] p-2.5 sm:p-3 hover:border-[#1d9bf0]/40 transition-all flex flex-col justify-between group relative shadow-2xs">
                <!-- Foto Menu -->
                <div>
                    <div class="relative w-full h-28 sm:h-36 rounded-xl overflow-hidden bg-[#f7f9f9] mb-2">
                        <img 
                            :src="$store.app.getProductPhoto(product.photo)" 
                            :alt="product.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            loading="lazy"
                        >
                        <span 
                            class="absolute top-1.5 left-1.5 px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider backdrop-blur-md"
                            :class="product.stock_badge === 'Best Seller' ? 'bg-[#1d9bf0] text-white shadow-xs' : (product.stock_badge === 'Favorit' ? 'bg-[#1d9bf0] text-white' : 'bg-[#0f1419]/70 text-white')"
                            x-text="product.stock_badge || product.category"
                        ></span>
                    </div>

                    <!-- Product Details -->
                    <div>
                        <h3 class="font-black text-xs sm:text-sm text-[#0f1419] truncate leading-tight group-hover:text-[#1d9bf0] transition-colors" x-text="product.title"></h3>
                        <p class="text-[10px] text-[#536471] line-clamp-1 mt-0.5 font-medium" x-text="product.description || 'Menu lezat siap saji'"></p>
                    </div>
                </div>

                <!-- Price & Action Buttons -->
                <div class="flex items-center justify-between pt-2 border-t border-[#eff3f4] mt-2">
                    <span class="text-xs sm:text-sm font-black text-[#0f1419]" x-text="formatRupiah(product.price)"></span>
                    
                    <div class="flex items-center gap-0.5">
                        <!-- Edit Button -->
                        <button 
                            @click="$store.app.openEditProductModal(product)"
                            class="p-1 sm:p-1.5 rounded-full hover:bg-[#e8f5fd] text-[#1d9bf0] transition-colors cursor-pointer"
                            title="Edit Menu"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>

                        <!-- Delete Button -->
                        <button 
                            @click="$store.app.openDeleteProductModal(product)"
                            class="p-1 sm:p-1.5 rounded-full hover:bg-rose-50 text-[#f4212e] transition-colors cursor-pointer"
                            title="Hapus Menu"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <template x-if="filteredProducts.length === 0">
        <div class="p-12 text-center bg-white rounded-3xl border border-[#eff3f4] my-6">
            <div class="w-16 h-16 rounded-full bg-[#e8f5fd] text-[#1d9bf0] flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
            </div>
            <h3 class="font-black text-base text-[#0f1419]">Menu Tidak Ditemukan</h3>
            <p class="text-xs text-[#536471] font-semibold mt-1">Coba gunakan kata kunci pencarian lain atau tambah menu baru.</p>
        </div>
    </template>

    <!-- ADD / EDIT PRODUCT MODAL (SLIDE UP BOTTOM SHEET ON MOBILE, CENTERED ON DESKTOP) -->
    <div 
        x-show="$store.app.productModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <!-- Backdrop -->
        <div 
            x-show="$store.app.productModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-[#0f1419]/60 backdrop-blur-xs transition-opacity"
            @click="$store.app.productModalOpen = false"
        ></div>

        <!-- Position: Bottom on Mobile (`items-end p-0`), Center on Desktop (`sm:items-center sm:p-4`) -->
        <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4 text-left">
            <div 
                x-show="$store.app.productModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                class="relative w-full max-w-lg bg-white rounded-t-3xl sm:rounded-3xl p-5 sm:p-8 shadow-2xl border-t sm:border border-[#eff3f4] text-left max-h-[92vh] sm:max-h-none overflow-y-auto custom-scrollbar"
            >
                <!-- Mobile Drag / Pull Indicator Handle -->
                <div class="w-12 h-1.5 bg-[#cfd9de] rounded-full mx-auto mb-3 sm:hidden"></div>

                <div class="flex items-center justify-between pb-3.5 border-b border-[#eff3f4]">
                    <h3 class="text-base sm:text-lg font-black text-[#0f1419]" x-text="$store.app.isEditingProduct ? 'Edit Menu Produk' : 'Tambah Menu Baru'"></h3>
                    <button @click="$store.app.productModalOpen = false" class="text-[#0f1419] hover:text-[#1d9bf0] p-1.5 rounded-full hover:bg-[#eff3f4] cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form @submit.prevent="$store.app.saveProduct()" class="space-y-4 mt-4">
                    <!-- Photo URL / Upload with Instant Preview -->
                    <div>
                        <label class="block text-xs font-bold text-[#0f1419] mb-1.5">Foto Menu (Preview Instan)</label>
                        <div class="flex items-center gap-4">
                            <template x-if="$store.app.productFormData.photoPreview">
                                <img 
                                    :src="$store.app.productFormData.photoPreview" 
                                    class="w-16 h-16 rounded-2xl object-cover border border-[#eff3f4] shadow-xs shrink-0"
                                >
                            </template>
                            <template x-if="!$store.app.productFormData.photoPreview">
                                <div class="w-16 h-16 rounded-2xl bg-[#f7f9f9] border border-[#eff3f4] flex items-center justify-center text-[#536471] shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            </template>
                            <input 
                                type="file" 
                                accept="image/*"
                                @change="$store.app.handleProductPhotoUpload($event)" 
                                class="flex-1 w-full text-xs text-[#0f1419] file:mr-3 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-[#e8f5fd] file:text-[#1d9bf0] hover:file:bg-[#d8eefc] cursor-pointer"
                            >
                        </div>
                    </div>

                    <!-- Judul Produk -->
                    <div>
                        <label class="block text-xs font-bold text-[#0f1419] mb-1">Nama Menu</label>
                        <input 
                            type="text" 
                            x-model="$store.app.productFormData.title" 
                            required
                            placeholder="Contoh: Es Kopi Susu Aren"
                            class="w-full px-4 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-xs sm:text-sm text-[#0f1419] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none font-semibold"
                        >
                    </div>

                    <!-- Category & Stock Badge -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-[#0f1419] mb-1">Kategori</label>
                            <select 
                                x-model="$store.app.productFormData.category"
                                class="w-full px-4 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-xs sm:text-sm text-[#0f1419] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none font-semibold"
                            >
                                <option value="Makanan">Makanan</option>
                                <option value="Minuman">Minuman</option>
                                <option value="Snack">Snack</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#0f1419] mb-1">Status Stok</label>
                            <select 
                                x-model="$store.app.productFormData.stock_badge"
                                class="w-full px-4 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-xs sm:text-sm text-[#0f1419] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none font-semibold"
                            >
                                <option value="Tersedia">Tersedia</option>
                                <option value="Best Seller">Best Seller</option>
                                <option value="Favorit">Favorit</option>
                                <option value="Habis">Habis</option>
                            </select>
                        </div>
                    </div>

                    <!-- Harga Jual (Rupiah) -->
                    <div>
                        <label class="block text-xs font-bold text-[#0f1419] mb-1">Harga Jual (Rp)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center font-bold text-xs text-[#536471]">Rp</span>
                            <input 
                                type="text" 
                                :value="formatNumber($store.app.productFormData.price)"
                                @input="$store.app.productFormData.price = $event.target.value.replace(/\D/g, '')"
                                required
                                placeholder="18.000"
                                class="w-full pl-10 pr-4 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-xs sm:text-sm text-[#0f1419] font-black focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none"
                            >
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label class="block text-xs font-bold text-[#0f1419] mb-1">Deskripsi Singkat</label>
                        <textarea 
                            x-model="$store.app.productFormData.description"
                            rows="2"
                            placeholder="Rincian rasa, porsi, atau bahan..."
                            class="w-full px-4 py-2 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-xs text-[#0f1419] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none font-medium"
                        ></textarea>
                    </div>

                    <!-- Action Buttons (Twitter Blue Submit Pill) -->
                    <div class="pt-3 flex gap-3">
                        <button 
                            type="button" 
                            @click="$store.app.productModalOpen = false"
                            class="flex-1 py-3.5 rounded-full bg-[#eff3f4] hover:bg-slate-200 text-[#0f1419] text-xs sm:text-sm font-black transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="flex-1 py-3.5 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs sm:text-sm font-black shadow-md shadow-[#1d9bf0]/25 transition-all cursor-pointer"
                        >
                            Simpan Menu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- DELETE PRODUCT CONFIRMATION MODAL (SLIDE UP BOTTOM SHEET ON MOBILE) -->
    <div 
        x-show="$store.app.deleteProductConfirmOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <div class="fixed inset-0 bg-[#0f1419]/60 backdrop-blur-xs" @click="$store.app.deleteProductConfirmOpen = false"></div>
        <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4 text-center">
            <div 
                x-show="$store.app.deleteProductConfirmOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                class="relative w-full max-w-sm bg-white rounded-t-3xl sm:rounded-3xl p-6 shadow-2xl text-center space-y-4 border-t sm:border border-[#eff3f4]"
            >
                <div class="w-12 h-1.5 bg-[#cfd9de] rounded-full mx-auto mb-2 sm:hidden"></div>
                <div class="w-12 h-12 rounded-full bg-rose-50 text-[#f4212e] flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </div>
                <h4 class="text-base font-black text-[#0f1419]">Hapus Menu Ini?</h4>
                <p class="text-xs text-[#0f1419] font-medium">Menu <strong class="text-[#0f1419] font-black" x-text="$store.app.productToDelete?.title"></strong> akan dinonaktifkan dari terminal kasir.</p>
                <div class="flex gap-2.5 pt-2">
                    <button @click="$store.app.deleteProductConfirmOpen = false" class="flex-1 py-3 rounded-full bg-[#eff3f4] font-black text-xs text-[#0f1419] cursor-pointer">Batal</button>
                    <button @click="$store.app.confirmDeleteProduct()" class="flex-1 py-3 rounded-full bg-[#f4212e] hover:bg-rose-700 font-black text-xs text-white cursor-pointer">Ya, Hapus</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
