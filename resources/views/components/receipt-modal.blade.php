<!-- Struk Pembayaran Modal (Twitter UI Theme - Mobile Bottom Sheet) -->
<div 
    x-show="$store.app.receiptModalOpen" 
    x-cloak 
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title" 
    role="dialog" 
    aria-modal="true"
>
    <!-- Backdrop -->
    <div 
        x-show="$store.app.receiptModalOpen"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-[#0f1419]/60 backdrop-blur-xs transition-opacity"
        @click="$store.app.receiptModalOpen = false"
    ></div>

    <!-- Position: Bottom on Mobile (`items-end p-0`), Center on Desktop (`sm:items-center sm:p-4`) -->
    <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4 text-center">
        <div 
            x-show="$store.app.receiptModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
            class="relative transform overflow-hidden rounded-t-3xl sm:rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-md p-5 sm:p-6 border-t sm:border border-[#eff3f4] max-h-[92vh] sm:max-h-none overflow-y-auto custom-scrollbar"
        >
            <!-- Mobile Pull Indicator Handle -->
            <div class="w-12 h-1.5 bg-[#cfd9de] rounded-full mx-auto mb-3 sm:hidden"></div>

            <!-- Header Modal -->
            <div class="flex items-center justify-between pb-3 border-b border-[#eff3f4]">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-[#e8f5fd] text-[#1d9bf0] font-black">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-black text-[#0f1419] leading-tight">Transaksi Berhasil</h3>
                        <p class="text-[11px] text-[#536471] font-semibold" x-text="$store.app.activeReceiptTransaction?.invoice_code"></p>
                    </div>
                </div>
                <button 
                    @click="$store.app.receiptModalOpen = false" 
                    class="text-[#0f1419] hover:text-[#1d9bf0] rounded-full p-1.5 hover:bg-[#eff3f4] transition-colors cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Kode Transaksi Unik -->
            <div class="my-4 text-center">
                <p class="text-xs text-[#536471] font-bold uppercase tracking-wider mb-1">KODE TRANSAKSI</p>
                <div class="inline-block px-6 py-2 bg-[#1d9bf0]/10 border border-[#1d9bf0]/20 rounded-xl">
                    <span class="text-4xl font-black text-[#1d9bf0] tracking-widest" x-text="String($store.app.activeReceiptTransaction?.id || 0).padStart(4, '0')"></span>
                </div>
            </div>

            <!-- Receipt Summary Card -->
            <div class="my-4 p-4 rounded-2xl bg-[#f7f9f9] border border-[#eff3f4] space-y-3.5 text-xs text-[#0f1419]">
                <template x-if="$store.app.activeReceiptTransaction?.status === 'pending' && $store.app.activeReceiptTransaction?.payment_method === 'cash'">
                    <div class="mb-2 bg-[#fff9e6] border border-[#ffcc00] text-[#856600] px-4 py-3 rounded-2xl text-[11px] font-bold text-center flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        BELUM DIBAYAR — Silakan bayar di Kasir Admin (dekat pintu keluar)
                    </div>
                </template>

                <!-- Store & Event Info -->
                <div class="flex items-start justify-between pb-3 border-b border-[#eff3f4]">
                    <div>
                        <h4 class="font-black text-sm text-[#0f1419]" x-text="$store.app.activeReceiptTransaction?.store_name || '-'"></h4>
                        <p class="text-[11px] text-[#536471] mt-0.5 font-medium" x-text="$store.app.getActiveEvent()?.name"></p>
                    </div>
                    <span 
                        class="px-3 py-0.5 rounded-full text-[10px] font-black uppercase bg-[#e8f5fd] text-[#1d9bf0] border border-[#bde2f9]"
                        x-text="$store.app.activeReceiptTransaction?.payment_method"
                    ></span>
                </div>

                <!-- Metadata List -->
                <div class="grid grid-cols-2 gap-2 text-[11px]">
                    <div>
                        <span class="text-[#536471] block text-[10px] font-bold">Waktu Transaksi</span>
                        <span class="font-bold text-[#0f1419]" x-text="formatDateTime($store.app.activeReceiptTransaction?.paid_at || $store.app.activeReceiptTransaction?.created_at)"></span>
                    </div>
                    <div>
                        <span class="text-[#536471] block text-[10px] font-bold">Kasir</span>
                        <span class="font-bold text-[#0f1419]" x-text="$store.app.activeReceiptTransaction?.cashier_name || 'Kasir'"></span>
                    </div>
                </div>

                <!-- Items List -->
                <div class="space-y-1.5 py-2 border-y border-[#eff3f4]">
                    <template x-for="(item, index) in ($store.app.activeReceiptTransaction?.items || [])" :key="item.id || item.product_id || index">
                        <div class="flex items-center justify-between text-xs">
                            <div class="truncate max-w-[200px]">
                                <span class="font-bold text-[#0f1419]" x-text="item.title"></span>
                                <span class="text-[11px] text-[#536471] block font-medium">
                                    <template x-if="item.is_negotiated">
                                        <s class="text-[#536471]/70 mr-1" x-text="formatRupiah(item.original_price)"></s>
                                    </template>
                                    <span x-text="`${item.qty} x ${formatRupiah(item.price)}`"></span>
                                </span>
                            </div>
                            <span class="font-black text-[#0f1419] shrink-0" x-text="formatRupiah(item.subtotal)"></span>
                        </div>
                    </template>
                </div>

                <!-- Totals (bagi hasil sengaja tidak ditampilkan: struk ini dilihat pembeli) -->
                <div class="space-y-2 pt-2 border-t border-[#eff3f4]">
                    <div class="flex justify-between items-center text-sm font-black text-[#0f1419]">
                        <span>Total Tagihan:</span>
                        <span class="text-base font-black text-[#0f1419]" x-text="formatRupiah($store.app.activeReceiptTransaction?.total_amount)"></span>
                    </div>

                    <template x-if="$store.app.activeReceiptTransaction?.payment_method === 'cash'">
                        <div class="space-y-1 pt-1 text-xs">
                            <div class="flex justify-between text-[#536471] font-semibold">
                                <span>Uang Diterima:</span>
                                <span class="font-bold text-[#0f1419]" x-text="formatRupiah($store.app.activeReceiptTransaction?.amount_paid)"></span>
                            </div>
                            <div class="flex justify-between font-black text-[#00ba7c]">
                                <span>Kembalian:</span>
                                <span x-text="formatRupiah($store.app.activeReceiptTransaction?.change_due)"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Action Buttons (Twitter Blue Submit Pill) -->
            <div class="mt-4 flex gap-2.5">
                <button 
                    @click="$store.app.printReceipt()"
                    type="button" 
                    class="flex-1 inline-flex justify-center items-center gap-2 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] px-5 py-3 text-xs sm:text-sm font-black text-white shadow-md shadow-[#1d9bf0]/25 transition-all cursor-pointer"
                >
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Cetak Struk / PDF</span>
                </button>
                <button 
                    @click="$store.app.receiptModalOpen = false" 
                    type="button" 
                    class="inline-flex justify-center items-center rounded-full bg-[#eff3f4] hover:bg-slate-200 px-5 py-3 text-xs sm:text-sm font-black text-[#0f1419] transition-colors cursor-pointer"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
