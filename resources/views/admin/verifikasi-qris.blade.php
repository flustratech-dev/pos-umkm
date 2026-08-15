@extends('layouts.app')

@section('title', 'Verifikasi QRIS EO')
@section('page_title', 'Verifikasi Pembayaran QRIS')

@section('content')
<div x-data="{
    proofZoomOpen: false,
    proofZoomUrl: '',

    openZoom(url) {
        this.proofZoomUrl = url;
        this.proofZoomOpen = true;
    }
}">
    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-[#0f1419] tracking-tight">Antrean Verifikasi QRIS</h2>
            <p class="text-xs sm:text-sm text-[#536471] font-semibold mt-0.5">Validasi bukti transfer QRIS statis sebelum transaksi dinyatakan berhasil</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-4 py-2 rounded-full text-xs font-black bg-[#e8f5fd] text-[#1d9bf0] border border-[#bde2f9] shadow-2xs">
                ⚡ Menunggu: <strong x-text="$store.app.transactions.filter(t => t.status === 'pending_verification').length"></strong> Transaksi
            </span>
        </div>
    </div>

    <!-- Info Box: Rule Verifikasi QRIS -->
    <div class="mb-6 p-4 rounded-2xl bg-[#f7f9f9] border border-[#eff3f4] flex items-start gap-3">
        <div class="p-2 rounded-full bg-[#e8f5fd] text-[#1d9bf0] shrink-0 mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div class="text-xs text-[#0f1419] space-y-1">
            <p class="font-black text-[#0f1419]">Prosedur Verifikasi QRIS Panitia EO:</p>
            <p class="text-[#536471] font-medium leading-relaxed">
                Cocokkan nominal pada bukti transfer pengunjung dengan notifikasi mutasi rekening/bank EO. Saat disetujui, sistem otomatis membagi hasil: <strong>75% Warung</strong>, <strong>25% EO Gross</strong> (dikurangi flat fee Rp1.000 Superadmin).
            </p>
        </div>
    </div>

    <!-- Verification Queue Cards List (Responsive for Mobile & Desktop) -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <template x-for="trx in $store.app.transactions.filter(t => t.status === 'pending_verification')" :key="trx.id">
            <div class="bg-white rounded-3xl border border-[#eff3f4] p-5 hover:border-[#bde2f9] transition-all flex flex-col justify-between shadow-2xs group relative">
                <div class="space-y-3">
                    <!-- Card Header: Invoice & Time -->
                    <div class="flex items-start justify-between pb-3 border-b border-[#eff3f4]">
                        <div>
                            <span class="text-xs font-black text-[#0f1419]" x-text="trx.invoice_code"></span>
                            <span class="text-[11px] text-[#536471] block font-medium" x-text="formatDateTime(trx.created_at)"></span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-800">
                            Menunggu Verifikasi
                        </span>
                    </div>

                    <!-- Store & Amount Info -->
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[#536471] font-semibold">Tenant Warung:</span>
                            <span class="font-black text-[#0f1419]" x-text="trx.store_name"></span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-[#536471] font-semibold">Kasir Input:</span>
                            <span class="font-bold text-[#0f1419]" x-text="trx.cashier_name || 'Kasir Stand'"></span>
                        </div>
                        <div class="flex items-center justify-between pt-1 text-sm font-black text-[#0f1419]">
                            <span>Nominal Tagihan:</span>
                            <span class="text-base text-[#1d9bf0]" x-text="formatRupiah(trx.total_amount)"></span>
                        </div>
                    </div>

                    <!-- Item Summary -->
                    <div class="p-3 bg-[#f7f9f9] rounded-2xl border border-[#eff3f4] space-y-1">
                        <span class="text-[10px] font-black text-[#536471] uppercase tracking-wider block">Daftar Item:</span>
                        <template x-for="item in trx.items" :key="item.product_id">
                            <div class="flex justify-between text-xs text-[#0f1419] font-medium">
                                <span class="truncate max-w-[170px]" x-text="`${item.qty}x ${item.title}`"></span>
                                <span class="font-bold" x-text="formatRupiah(item.subtotal)"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Proof Image Preview (Click to Zoom) -->
                    <div>
                        <span class="text-[11px] font-bold text-[#0f1419] block mb-1.5">Bukti Transfer Pengunjung:</span>
                        <div 
                            @click="openZoom(trx.payment_proof || 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=500&auto=format&fit=crop&q=80')"
                            class="relative rounded-2xl overflow-hidden border border-[#eff3f4] bg-[#f7f9f9] group-hover:border-[#bde2f9] cursor-pointer h-36 flex items-center justify-center transition-all"
                        >
                            <img 
                                :src="trx.payment_proof || 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=500&auto=format&fit=crop&q=80'" 
                                class="w-full h-full object-cover"
                            >
                            <div class="absolute inset-0 bg-[#0f1419]/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity text-white text-xs font-bold gap-1.5 backdrop-blur-2xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
                                <span>Klik Zoom Bukti</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons: Approve / Reject (Twitter Style Rounded Full) -->
                <div class="pt-4 border-t border-[#eff3f4] flex gap-2.5 mt-4">
                    <!-- Reject Button -->
                    <button 
                        @click="$store.app.openRejectModal(trx)"
                        class="flex-1 py-2.5 rounded-full bg-[#eff3f4] hover:bg-rose-50 text-[#0f1419] hover:text-[#f4212e] text-xs font-black transition-all border border-[#eff3f4] flex items-center justify-center gap-1.5 cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-[#f4212e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        <span>Tolak</span>
                    </button>

                    <!-- Approve Button (Twitter Blue) -->
                    <button 
                        @click="$store.app.approveQris(trx.id)"
                        class="flex-1 py-2.5 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs font-black shadow-md shadow-[#1d9bf0]/25 transition-all flex items-center justify-center gap-1.5 active:scale-95 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        <span>Setujui</span>
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <template x-if="$store.app.transactions.filter(t => t.status === 'pending_verification').length === 0">
        <div class="bg-white rounded-3xl border border-[#eff3f4] p-12 text-center max-w-md mx-auto my-8 shadow-2xs">
            <div class="w-16 h-16 bg-[#e8f5fd] rounded-full text-[#1d9bf0] flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h4 class="text-sm font-black text-[#0f1419]">Antrean Verifikasi Kosong</h4>
            <p class="text-xs text-[#536471] font-semibold mt-1">Semua transaksi QRIS pengunjung telah selesai diverifikasi oleh panitia.</p>
        </div>
    </template>

    <!-- REJECT MODAL WITH REASON (SLIDE UP BOTTOM SHEET ON MOBILE, CENTERED ON DESKTOP) -->
    <div 
        x-show="$store.app.rejectModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <!-- Backdrop -->
        <div 
            x-show="$store.app.rejectModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-[#0f1419]/60 backdrop-blur-xs transition-opacity" 
            @click="$store.app.rejectModalOpen = false"
        ></div>

        <!-- Position: Bottom on Mobile (`items-end p-0`), Center on Desktop (`sm:items-center sm:p-4`) -->
        <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4 text-left">
            <div 
                x-show="$store.app.rejectModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                class="relative w-full max-w-md bg-white rounded-t-3xl sm:rounded-3xl p-6 sm:p-8 shadow-2xl space-y-4 border-t sm:border border-[#eff3f4] text-left max-h-[92vh] sm:max-h-none overflow-y-auto custom-scrollbar"
            >
                <!-- Mobile Pull Indicator Handle -->
                <div class="w-12 h-1.5 bg-[#cfd9de] rounded-full mx-auto mb-2 sm:hidden"></div>

                <div class="flex items-center justify-between pb-3 border-b border-[#eff3f4]">
                    <h3 class="text-base font-black text-[#0f1419]">Tolak Verifikasi QRIS</h3>
                    <button @click="$store.app.rejectModalOpen = false" class="text-[#0f1419] hover:text-[#1d9bf0] p-1.5 rounded-full hover:bg-[#eff3f4] cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="space-y-3">
                    <p class="text-xs text-[#536471] leading-relaxed">
                        Tolak transaksi <strong class="text-[#0f1419] font-black" x-text="$store.app.transactionToReject?.invoice_code"></strong> sebesar <strong class="text-[#0f1419] font-black" x-text="formatRupiah($store.app.transactionToReject?.total_amount)"></strong>?
                    </p>

                    <div>
                        <label class="block text-xs font-bold text-[#0f1419] mb-1">Pilih Alasan Penolakan</label>
                        <select 
                            x-model="$store.app.rejectReason"
                            class="w-full px-4 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-xs text-[#0f1419] focus:ring-2 focus:ring-[#f4212e] focus:outline-none font-semibold cursor-pointer"
                        >
                            <option value="Bukti transfer tidak jelas / buram">Bukti transfer tidak jelas / buram</option>
                            <option value="Nominal transfer tidak sesuai tagihan">Nominal transfer tidak sesuai tagihan</option>
                            <option value="Mutasi belum masuk ke rekening bank panitia">Mutasi belum masuk ke rekening bank panitia</option>
                            <option value="Bukti transfer palsu / duplikat">Bukti transfer palsu / duplikat</option>
                        </select>
                    </div>

                    <div class="pt-2 flex gap-3">
                        <button 
                            type="button" 
                            @click="$store.app.rejectModalOpen = false"
                            class="flex-1 py-3 rounded-full bg-[#eff3f4] text-[#0f1419] text-xs font-black transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                        <button 
                            type="button" 
                            @click="$store.app.confirmRejectQris()"
                            class="flex-1 py-3 rounded-full bg-[#f4212e] hover:bg-rose-700 text-white text-xs font-black shadow-md shadow-rose-600/30 transition-all cursor-pointer"
                        >
                            Konfirmasi Tolak
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PROOF ZOOM MODAL (SLIDE UP BOTTOM SHEET ON MOBILE, CENTERED ON DESKTOP) -->
    <div 
        x-show="proofZoomOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <div 
            x-show="proofZoomOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-[#0f1419]/80 backdrop-blur-md transition-opacity" 
            @click="proofZoomOpen = false"
        ></div>

        <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4 text-center">
            <div 
                x-show="proofZoomOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                class="relative max-w-md w-full bg-white rounded-t-3xl sm:rounded-3xl p-4 sm:p-6 shadow-2xl space-y-3 border-t sm:border border-[#eff3f4] text-left max-h-[92vh] sm:max-h-none overflow-y-auto custom-scrollbar"
            >
                <div class="w-12 h-1.5 bg-[#cfd9de] rounded-full mx-auto mb-2 sm:hidden"></div>

                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-black text-[#0f1419]">Foto Bukti Pembayaran QRIS</h4>
                    <button @click="proofZoomOpen = false" class="text-[#0f1419] hover:text-[#1d9bf0] p-1.5 rounded-full hover:bg-[#eff3f4] cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="rounded-2xl overflow-hidden border border-[#eff3f4]">
                    <img :src="proofZoomUrl" class="w-full h-auto max-h-[65vh] object-contain mx-auto">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
