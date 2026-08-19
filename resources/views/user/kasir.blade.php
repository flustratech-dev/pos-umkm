@extends('layouts.app')

@section('title', 'Kasir & Checkout POS')

@section('content')
<div x-data="{
    searchQuery: '',
    selectedCategory: 'all',
    
    get storeProducts() {
        const storeId = $store.app.getCurrentStore()?.id;
        return $store.app.products.filter(p => {
            const matchesStore = storeId ? p.store_id === storeId : true;
            const matchesSearch = p.title.toLowerCase().includes(this.searchQuery.toLowerCase());
            const matchesCat = this.selectedCategory === 'all' || p.category === this.selectedCategory;
            return matchesStore && matchesSearch && matchesCat && p.is_active;
        });
    },

    get uniqueCode() {
        return $store.app.getCurrentStore()?.id || 0;
    }
}" class="space-y-4">

    <!-- Header Section (Twitter UI) -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-[#0f1419] tracking-tight flex items-center gap-2">
                <span>Terminal Kasir</span>
                <span class="text-xs px-3.5 py-0.5 rounded-full bg-[#e8f5fd] text-[#1d9bf0] font-black border border-[#bde2f9]" x-text="$store.app.getCurrentStore()?.name"></span>
            </h2>
            <p class="text-xs text-[#0f1419] font-medium mt-0.5">Pilih menu untuk menambahkan ke pesanan kasir</p>
        </div>

        <!-- Cart Quick Toggle (Twitter Blue Pill Button) -->
        <button 
            x-show="$store.app.activeStoreEventActive"
            x-cloak
            @click="$store.app.isCheckoutOpen = true"
            type="button" 
            class="relative inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs sm:text-sm font-black shadow-md shadow-[#1d9bf0]/25 transition-all active:scale-95 cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <span>Buka Keranjang</span>
            <span 
                x-show="$store.app.cartItemCount > 0" 
                x-cloak
                class="px-2 py-0.5 text-xs font-black bg-white text-[#1d9bf0] rounded-full shadow-2xs"
                x-text="$store.app.cartItemCount"
            ></span>
        </button>
    </div>

    <!-- Readonly Banner -->
    <div x-show="!$store.app.activeStoreEventActive" x-cloak class="p-4 rounded-2xl bg-[#f4212e]/10 border border-[#f4212e]/20 flex gap-3">
        <svg class="w-5 h-5 text-[#f4212e] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <div>
            <h3 class="text-sm font-black text-[#f4212e]">Mesin Kasir Dikunci</h3>
            <p class="text-xs text-[#f4212e] mt-1 font-medium">Event untuk warung ini telah berakhir. Anda tidak dapat membuat transaksi baru. Transaksi lama tetap dapat dilihat di riwayat.</p>
        </div>
    </div>

    <!-- Search & Filter Tabs -->
    <div class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between bg-white p-3.5 rounded-3xl border border-[#eff3f4] shadow-xs">
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#536471]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input 
                type="text" 
                x-model="searchQuery"
                placeholder="Cari menu cepat..." 
                class="w-full pl-9 pr-4 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-full text-xs sm:text-sm text-[#0f1419] placeholder-[#536471] focus:outline-none focus:ring-2 focus:ring-[#1d9bf0] focus:bg-white transition-all font-semibold"
            >
        </div>

        <!-- Mobile Layout (Semua 1 Lebar, 3 Berjejer) -->
        <div class="flex flex-col gap-2 md:hidden">
            <button 
                @click="selectedCategory = 'all'" 
                class="w-full py-2.5 px-4 rounded-2xl text-xs font-black transition-all text-center cursor-pointer shadow-2xs"
                :class="selectedCategory === 'all' ? 'bg-[#1d9bf0] text-white shadow-xs' : 'bg-[#f7f9f9] hover:bg-[#eff3f4] text-[#0f1419] border border-[#eff3f4]'"
            >
                ✨ Semua Produk
            </button>
            <div class="grid grid-cols-2 gap-2">
                @foreach (\App\Models\Product::CATEGORIES as $category => $icon)
                <button 
                    @click="selectedCategory = '{{ $category }}'" 
                    class="py-2.5 px-2 rounded-2xl text-xs font-black transition-all text-center cursor-pointer truncate shadow-2xs"
                    :class="selectedCategory === '{{ $category }}' ? 'bg-[#1d9bf0] text-white shadow-xs' : 'bg-[#f7f9f9] hover:bg-[#eff3f4] text-[#0f1419] border border-[#eff3f4]'"
                >
                    {{ $icon }} {{ $category }}
                </button>
                @endforeach
            </div>
        </div>

        <!-- Desktop Layout (Baris Sejajar Asli) -->
        <div class="hidden md:flex flex-wrap items-center justify-end gap-1.5">
            <button 
                @click="selectedCategory = 'all'" 
                class="px-4 py-2 rounded-full text-xs font-black transition-all shrink-0 cursor-pointer"
                :class="selectedCategory === 'all' ? 'bg-[#1d9bf0] text-white shadow-sm' : 'bg-[#eff3f4] text-[#0f1419] hover:bg-[#e8f5fd] hover:text-[#1d9bf0]'"
            >
                Semua
            </button>
            @foreach (\App\Models\Product::CATEGORIES as $category => $icon)
            <button 
                @click="selectedCategory = '{{ $category }}'" 
                class="px-4 py-2 rounded-full text-xs font-black transition-all shrink-0 cursor-pointer"
                :class="selectedCategory === '{{ $category }}' ? 'bg-[#1d9bf0] text-white shadow-sm' : 'bg-[#eff3f4] text-[#0f1419] hover:bg-[#e8f5fd] hover:text-[#1d9bf0]'"
            >
                {{ $icon }} {{ $category }}
            </button>
            @endforeach
        </div>
    </div>

    <!-- Product Catalog (Matching Layout & Sizing with Kelola Produk) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 gap-2.5 sm:gap-3.5">
        <template x-for="product in storeProducts" :key="product.id">
            <div 
                @click="if($store.app.activeStoreEventActive) $store.app.addToCart(product)"
                class="bg-white rounded-2xl border border-[#eff3f4] p-2.5 sm:p-3 hover:border-[#1d9bf0]/40 transition-all flex flex-col justify-between group relative shadow-2xs"
                :class="$store.app.activeStoreEventActive ? 'cursor-pointer' : 'cursor-not-allowed opacity-80 grayscale-[20%]'"
            >
                <!-- Foto Menu -->
                <div>
                    <div class="relative w-full h-28 sm:h-36 rounded-xl overflow-hidden bg-[#f7f9f9] mb-2">
                        <img 
                            :src="$store.app.getProductPhoto(product.photo)" 
                            :alt="product.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            decoding="async"
                            loading="eager"
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

                <!-- Price & Quick Add Button -->
                <div class="flex items-center justify-between pt-2 border-t border-[#eff3f4] mt-2 gap-1.5">
                    <span class="text-xs sm:text-sm font-black text-[#0f1419]" x-text="product.is_negotiable ? `${formatRupiah($store.app.priceRangeOf(product).min)} - ${formatRupiah($store.app.priceRangeOf(product).max)}` : formatRupiah(product.price)"></span>
                    
                    <button 
                        type="button"
                        x-show="$store.app.activeStoreEventActive"
                        x-cloak
                        class="px-2.5 py-1 rounded-full bg-[#1d9bf0] text-white hover:bg-[#1a8cd8] active:scale-95 flex items-center gap-1 font-bold text-[10px] sm:text-xs transition-all shadow-2xs shrink-0 cursor-pointer"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        <span>Tambah</span>
                    </button>
                    <button 
                        type="button"
                        x-show="!$store.app.activeStoreEventActive"
                        x-cloak
                        disabled
                        class="px-2.5 py-1 rounded-full bg-[#eff3f4] text-[#536471] flex items-center gap-1 font-bold text-[10px] sm:text-xs shrink-0 cursor-not-allowed opacity-70"
                    >
                        <span>Selesai</span>
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <template x-if="storeProducts.length === 0">
        <div class="bg-white rounded-3xl border border-[#eff3f4] p-10 text-center max-w-md mx-auto my-8">
            <div class="w-14 h-14 mx-auto rounded-full bg-[#f7f9f9] text-[#536471] flex items-center justify-center mb-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
            <h3 class="text-sm font-black text-[#0f1419]">Katalog Menu Masih Kosong</h3>
            <p class="text-xs text-[#536471] font-medium mt-1 mb-4">Belum ada menu yang didaftarkan pada stand ini. Tambahkan produk sekarang untuk mulai berjualan.</p>
            <a href="/user/produk" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs font-black transition-all shadow-xs">
                <span>+ Tambah Produk Baru</span>
            </a>
        </div>
    </template>

    <!-- SLIDE-OVER DRAWER CHECKOUT (Twitter UI) -->
    <div 
        x-show="$store.app.isCheckoutOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-hidden"
    >
        <!-- Backdrop -->
        <div 
            x-show="$store.app.isCheckoutOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-[#0f1419]/60 backdrop-blur-xs transition-opacity"
            @click="$store.app.isCheckoutOpen = false"
        ></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div 
                x-show="$store.app.isCheckoutOpen"
                x-transition:enter="transform transition ease-in-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in-out duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="w-screen max-w-md bg-white shadow-2xl flex flex-col justify-between"
            >
                <!-- Drawer Header -->
                <div class="p-5 border-b border-[#eff3f4] flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-black text-[#0f1419]">Keranjang Pesanan</h3>
                        <p class="text-xs text-[#536471]" x-text="`${$store.app.cartItemCount} item dalam antrean`"></p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button 
                            @click="$store.app.clearCart()"
                            x-show="$store.app.cart.length > 0"
                            x-cloak
                            class="text-xs font-black text-[#f4212e] hover:underline px-2 py-1 cursor-pointer"
                        >
                            Kosongkan
                        </button>
                        <button 
                            @click="$store.app.isCheckoutOpen = false"
                            class="text-[#0f1419] hover:text-[#1d9bf0] p-1.5 rounded-full hover:bg-[#eff3f4] cursor-pointer"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Drawer Content: Cart Items List -->
                <div class="flex-1 overflow-y-auto p-5 space-y-3 custom-scrollbar">
                    <template x-if="$store.app.cart.length === 0">
                        <div class="py-16 text-center text-[#536471]">
                            <svg class="w-14 h-14 mx-auto mb-3 opacity-30 text-[#1d9bf0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            <p class="text-sm font-black text-[#0f1419]">Keranjang masih kosong</p>
                            <p class="text-xs text-[#536471] mt-1">Ketuk menu di sebelah kiri untuk menambahkan pesanan.</p>
                        </div>
                    </template>

                    <template x-for="item in $store.app.cart" :key="item.product.id">
                        <div class="p-3 rounded-2xl bg-[#f7f9f9] border border-[#eff3f4] flex items-center justify-between gap-3">
                            <img :src="$store.app.getProductPhoto(item.product.photo)" class="w-12 h-12 rounded-xl object-cover shrink-0 border border-[#eff3f4]">
                            <div class="flex-1 min-w-0">
                                <h5 class="font-black text-xs sm:text-sm text-[#0f1419] truncate" x-text="item.product.title"></h5>

                                <!-- Harga pas: tampil apa adanya -->
                                <template x-if="!item.product.is_negotiable">
                                    <p class="text-xs font-black text-[#1d9bf0]" x-text="formatRupiah($store.app.cartItemPrice(item))"></p>
                                </template>

                                <!-- Harga tawar: kasir mengisi harga deal, dibatasi rentang produk -->
                                <template x-if="item.product.is_negotiable">
                                    <div class="mt-1 space-y-1">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[9px] font-black uppercase tracking-wider border border-amber-200 shrink-0">Nego</span>
                                            <div class="relative flex-1 min-w-0">
                                                <span class="absolute inset-y-0 left-0 pl-2 flex items-center text-[10px] font-bold text-[#536471]">Rp</span>
                                                <input
                                                    type="text"
                                                    inputmode="numeric"
                                                    x-effect="if (document.activeElement !== $el) $el.value = formatNumber($store.app.cartItemPrice(item))"
                                                    @input="item.price = $event.target.value.replace(/\D/g, '')"
                                                    @change="$store.app.updateCartPrice(item.product.id, $event.target.value)"
                                                    @blur="$el.value = formatNumber($store.app.cartItemPrice(item))"
                                                    class="w-full pl-7 pr-2 py-1 bg-white border border-[#cfd9de] rounded-lg text-xs font-black text-[#1d9bf0] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none"
                                                >
                                            </div>
                                        </div>
                                        <p class="text-[9px] text-[#536471] font-bold" x-text="`Rentang ${formatRupiah($store.app.priceRangeOf(item.product).min)} - ${formatRupiah($store.app.priceRangeOf(item.product).max)}`"></p>
                                    </div>
                                </template>
                            </div>

                            <!-- Qty Controls (Twitter Style Pill) -->
                            <div class="flex items-center gap-1.5 bg-white border border-[#eff3f4] rounded-full p-1 shrink-0">
                                <button 
                                    @click="$store.app.updateCartQty(item.product.id, -1)" 
                                    class="w-6 h-6 rounded-full bg-[#eff3f4] hover:bg-slate-200 text-[#0f1419] flex items-center justify-center font-black text-xs cursor-pointer"
                                >
                                    -
                                </button>
                                <span class="w-6 text-center text-xs font-black text-[#0f1419]" x-text="item.qty"></span>
                                <button 
                                    @click="$store.app.updateCartQty(item.product.id, 1)" 
                                    class="w-6 h-6 rounded-full bg-[#e8f5fd] hover:bg-[#1d9bf0] hover:text-white text-[#1d9bf0] flex items-center justify-center font-black text-xs transition-colors cursor-pointer"
                                >
                                    +
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Payment Panel Footer -->
                <div x-show="$store.app.cart.length > 0" x-cloak class="p-5 border-t border-[#eff3f4] bg-[#f7f9f9] space-y-4">
                    <!-- Total Bill -->
                    <div x-show="$store.app.cartNegotiatedDiscount > 0" x-cloak class="flex items-center justify-between text-xs">
                        <span class="font-bold text-[#536471]">Potongan hasil nego</span>
                        <span class="font-black text-[#00ba7c]" x-text="`- ${formatRupiah($store.app.cartNegotiatedDiscount)}`"></span>
                    </div>

                    <div class="flex items-center justify-between pb-3 border-b border-[#eff3f4]">
                        <span class="text-xs font-bold text-[#0f1419] uppercase tracking-wider">Total Tagihan</span>
                        <span class="text-2xl font-black text-[#0f1419]" x-text="formatRupiah($store.app.cartTotal + ($store.app.activePaymentTab === 'qris' ? uniqueCode : 0))"></span>
                    </div>

                    <!-- Payment Method Tabs: CASH vs QRIS (Twitter Blue Pills) -->
                    <div>
                        <label class="block text-xs font-bold text-[#0f1419] mb-2">Metode Pembayaran:</label>
                        <div class="grid grid-cols-2 gap-2 bg-[#eff3f4] p-1 rounded-full">
                            <button 
                                @click="$store.app.activePaymentTab = 'cash'"
                                type="button" 
                                class="py-2.5 rounded-full text-xs font-black transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                                :class="$store.app.activePaymentTab === 'cash' ? 'bg-[#1d9bf0] text-white shadow-sm' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
                            >
                                💵 Cash / Tunai
                            </button>
                            <button 
                                @click="$store.app.activePaymentTab = 'qris'; $store.app.generateDynamicQris()"
                                type="button" 
                                class="py-2.5 rounded-full text-xs font-black transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                                :class="$store.app.activePaymentTab === 'qris' ? 'bg-[#1d9bf0] text-white shadow-sm' : 'text-[#0f1419] hover:text-[#1d9bf0]'"
                            >
                                📱 QRIS
                            </button>
                        </div>
                    </div>

                    <!-- TAB 1: CASH PAYMENT (Twitter Blue CTA) -->
                    <div x-show="$store.app.activePaymentTab === 'cash'" x-cloak class="space-y-3 pt-1">
                        <div>
                            <label class="block text-xs font-bold text-[#0f1419] mb-1">Uang Diterima (Rp)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center font-black text-xs text-[#536471]">Rp</span>
                                <input 
                                    type="text" 
                                    :value="formatNumber($store.app.cashAmountPaid)"
                                    @input="$store.app.cashAmountPaid = $event.target.value.replace(/\D/g, '')"
                                    placeholder="Ketik nominal uang customer..."
                                    class="w-full pl-10 pr-4 py-2.5 bg-white border border-[#eff3f4] rounded-full text-sm font-black text-[#0f1419] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none"
                                >
                            </div>
                        </div>

                        <!-- Quick Nominal Preset Chips (Twitter Pills) -->
                        <div class="flex flex-wrap gap-1.5">
                            <button 
                                @click="$store.app.setCashPreset($store.app.cartTotal)"
                                type="button" 
                                class="px-3.5 py-1.5 rounded-full text-xs font-black bg-white border border-[#eff3f4] hover:bg-[#e8f5fd] hover:text-[#1d9bf0] text-[#0f1419] shadow-2xs transition-colors cursor-pointer"
                            >
                                Uang Pas
                            </button>
                            <button 
                                @click="$store.app.setCashPreset(20000)"
                                type="button" 
                                class="px-3.5 py-1.5 rounded-full text-xs font-black bg-white border border-[#eff3f4] hover:bg-[#e8f5fd] hover:text-[#1d9bf0] text-[#0f1419] shadow-2xs transition-colors cursor-pointer"
                            >
                                20.000
                            </button>
                            <button 
                                @click="$store.app.setCashPreset(50000)"
                                type="button" 
                                class="px-3.5 py-1.5 rounded-full text-xs font-black bg-white border border-[#eff3f4] hover:bg-[#e8f5fd] hover:text-[#1d9bf0] text-[#0f1419] shadow-2xs transition-colors cursor-pointer"
                            >
                                50.000
                            </button>
                            <button 
                                @click="$store.app.setCashPreset(100000)"
                                type="button" 
                                class="px-3.5 py-1.5 rounded-full text-xs font-black bg-white border border-[#eff3f4] hover:bg-[#e8f5fd] hover:text-[#1d9bf0] text-[#0f1419] shadow-2xs transition-colors cursor-pointer"
                            >
                                100.000
                            </button>
                        </div>

                        <!-- Live Change Display & Validation -->
                        <div class="p-3.5 rounded-2xl border" :class="$store.app.isCashValid ? 'bg-[#e8f5fd] border-[#bde2f9]' : 'bg-rose-50/50 border-[#f4212e]/30'">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-black" :class="$store.app.isCashValid ? 'text-[#1d9bf0]' : 'text-[#f4212e]'">
                                    <template x-if="$store.app.isCashValid">
                                        <span>Kembalian:</span>
                                    </template>
                                    <template x-if="!$store.app.isCashValid">
                                        <span>Uang Kurang:</span>
                                    </template>
                                </span>
                                <span class="text-lg font-black" :class="$store.app.isCashValid ? 'text-[#0f1419]' : 'text-[#f4212e]'" x-text="formatRupiah(Math.abs((parseFloat($store.app.cashAmountPaid) || 0) - $store.app.cartTotal))"></span>
                            </div>
                        </div>

                        <!-- Confirm Cash Checkout Button (Twitter Blue Pill) -->
                        <button 
                            @click="$store.app.processCashCheckout()"
                            type="button" 
                            :disabled="!$store.app.isCashValid"
                            class="w-full py-3.5 px-4 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white font-black text-sm shadow-md shadow-[#1d9bf0]/25 transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                        >
                            <span>Bayar Tunai & Cetak Nota</span>
                        </button>
                    </div>

                    <!-- TAB 2: QRIS PAYMENT (Twitter Blue CTA) -->
                    <div x-show="$store.app.activePaymentTab === 'qris'" x-cloak class="space-y-3 pt-1">
                        <!-- QRIS Display Box -->
                        <div class="bg-[#f7f9f9] border border-[#eff3f4] rounded-2xl p-4 text-center">
                            <template x-if="$store.app.getCurrentStore()?.use_dynamic_qris && window.__ACTIVE_EVENT__?.qris_payload">
                                <div>
                                    <span class="text-[11px] font-bold text-[#0f1419] block uppercase">Scan QRIS Pembayaran</span>
                                    <div x-show="$store.app.dynamicQrisLoading" class="flex flex-col items-center justify-center py-6 h-40">
                                        <svg class="w-8 h-8 text-[#1d9bf0] animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <p class="text-xs font-bold text-[#536471] mt-2">Menyiapkan QRIS...</p>
                                    </div>
                                    <div x-show="!$store.app.dynamicQrisLoading && $store.app.dynamicQrisDataUrl" class="flex flex-col items-center justify-center py-1">
                                        <img :src="$store.app.dynamicQrisDataUrl" alt="QRIS Code" class="w-40 h-40 object-contain rounded-xl border border-[#eff3f4] p-1 bg-white">
                                        <p class="text-[11px] font-bold text-[#1d9bf0] mt-2 tracking-wide uppercase">NMID: {{ \App\Models\Event::getActive() ? \App\Models\Event::getActive()->name : '-' }}</p>
                                        <p class="text-xs font-black text-[#0f1419] mt-1" x-text="window.__ACTIVE_EVENT__.name"></p>
                                        <p class="text-[10px] text-[#00ba7c] font-bold mt-2 bg-emerald-50 border border-emerald-200/60 px-2.5 py-1.5 rounded-lg">Nominal otomatis terisi: <span x-text="formatRupiah($store.app.cartTotal + uniqueCode)"></span></p>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!$store.app.getCurrentStore()?.use_dynamic_qris || !window.__ACTIVE_EVENT__?.qris_payload">
                                <div>
                                    <span class="text-[11px] font-bold text-[#0f1419] block uppercase">Scan QRIS Pembayaran</span>
                                    <template x-if="window.__ACTIVE_EVENT__ && window.__ACTIVE_EVENT__.qris_image_url">
                                        <div class="flex flex-col items-center justify-center py-1">
                                            <img :src="window.__ACTIVE_EVENT__.qris_image_url" alt="QRIS Code" class="w-40 h-40 object-contain rounded-xl border border-[#eff3f4] p-1 bg-white">
                                            <p class="text-xs font-black text-[#0f1419] mt-2" x-text="window.__ACTIVE_EVENT__.name"></p>
                                            
                                            <!-- Alert Input Manual untuk QRIS Statis -->
                                            <div class="mt-2.5 p-3 bg-amber-50 border border-amber-200 rounded-xl text-left flex items-start gap-2.5 text-amber-900 w-full">
                                                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                <div>
                                                    <p class="text-xs font-bold">Input Nominal Manual</p>
                                                    <p class="text-[11px] text-amber-800 mt-0.5 leading-snug">Customer wajib memasukkan nominal transfer secara manual sebesar <span class="font-black text-amber-950" x-text="formatRupiah($store.app.cartTotal + uniqueCode)"></span> (termasuk kode unik).</p>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!window.__ACTIVE_EVENT__ || (!window.__ACTIVE_EVENT__.qris_image_url && !window.__ACTIVE_EVENT__.qris_payload)">
                                        <div class="py-6 flex flex-col items-center justify-center border-2 border-dashed border-[#eff3f4] rounded-xl">
                                            <svg class="w-8 h-8 text-[#536471] mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                            <p class="text-xs font-bold text-[#536471]">QRIS Belum Tersedia</p>
                                            <p class="text-[10px] text-[#536471] mt-1">Harap hubungi Admin EO untuk menambahkan QRIS Event.</p>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- Upload Bukti Pembayaran QRIS (Foto / Struk untuk Arsip Laporan) -->
                        <div class="bg-white border border-[#eff3f4] rounded-2xl p-3.5 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-[#0f1419]">
                                    Upload Bukti Transfer <span class="text-[10px] text-[#536471] font-normal">(Opsional / Arsip Laporan)</span>
                                </label>
                                <template x-if="$store.app.qrisProofFile">
                                    <span class="text-[10px] text-[#00ba7c] font-black flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                        Terlampir
                                    </span>
                                </template>
                            </div>

                            <!-- Hidden Inputs: Direct Camera & Gallery Picker -->
                            <input 
                                type="file" 
                                id="qris_proof_camera" 
                                accept="image/*" 
                                capture="environment" 
                                class="hidden" 
                                @change="$store.app.handleQrisProofUpload($event)"
                            >
                            <input 
                                type="file" 
                                id="qris_proof_gallery" 
                                accept="image/*" 
                                class="hidden" 
                                @change="$store.app.handleQrisProofUpload($event)"
                            >

                            <!-- If Proof Attached: Preview Box -->
                            <template x-if="$store.app.qrisProofPreview">
                                <div class="relative w-full rounded-xl border border-[#bde2f9] bg-[#e8f5fd]/50 p-2.5 flex items-center gap-3">
                                    <img :src="$store.app.qrisProofPreview" class="w-14 h-14 object-cover rounded-lg border border-white shadow-xs shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-black text-[#0f1419] truncate" x-text="$store.app.qrisProofFile?.name || 'Bukti Transfer QRIS'"></p>
                                        <p class="text-[10px] text-[#536471] mt-0.5">Tersimpan di arsip laporan.</p>
                                        <button 
                                            @click="$store.app.removeQrisProof()"
                                            type="button" 
                                            class="text-[11px] text-[#f4212e] font-black hover:underline mt-1 cursor-pointer inline-flex items-center gap-1"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            <span>Hapus / Ganti Foto</span>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <!-- If No Proof Attached: 2 Buttons (Camera & Gallery) -->
                            <template x-if="!$store.app.qrisProofPreview">
                                <div class="flex gap-2">
                                    <!-- Option 1: Direct Camera Trigger (HTML5 capture="environment" for Android & iOS) -->
                                    <button 
                                        type="button" 
                                        onclick="document.getElementById('qris_proof_camera').click()"
                                        class="flex-1 py-2.5 px-3 rounded-xl bg-[#e8f5fd] hover:bg-[#d8eefc] text-[#1d9bf0] font-black text-xs flex items-center justify-center gap-1.5 transition-all border border-[#bde2f9] active:scale-95 cursor-pointer shadow-2xs"
                                    >
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        <span>Buka Kamera</span>
                                    </button>

                                    <!-- Option 2: Gallery Picker -->
                                    <button 
                                        type="button" 
                                        onclick="document.getElementById('qris_proof_gallery').click()"
                                        class="flex-1 py-2.5 px-3 rounded-xl bg-white hover:bg-[#f7f9f9] text-[#0f1419] font-bold text-xs flex items-center justify-center gap-1.5 transition-all border border-[#eff3f4] active:scale-95 cursor-pointer shadow-2xs"
                                    >
                                        <svg class="w-4 h-4 shrink-0 text-[#536471]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <span>Pilih Galeri</span>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <!-- Confirm QRIS Button (Twitter Blue Pill) -->
                        <button 
                            @click="$store.app.processQrisCheckout()"
                            type="button" 
                            class="w-full py-3.5 px-4 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white font-black text-sm shadow-md shadow-[#1d9bf0]/25 transition-all flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <span>Bayar & Cetak Nota Otomatis</span>
                        </button>
                        <p class="text-[10px] text-[#536471] text-center italic font-medium">
                            *Pembayaran QRIS otomatis tersimpan langsung ke sistem & laporan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
