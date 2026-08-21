@extends('layouts.app')

@section('title', 'Verifikasi QRIS EO')
@section('page_title', 'Verifikasi Pembayaran QRIS')

@section('content')
<div x-data="{
    proofZoomOpen: false,
    proofZoomUrl: '',
    searchQuery: '',

    openZoom(url) {
        this.proofZoomUrl = url;
        this.proofZoomOpen = true;
    },

    get filteredPending() {
        const list = this.$store.app.transactions.filter(t => t.status === 'pending_verification');
        if (!this.searchQuery) return list;
        const q = this.searchQuery.toLowerCase().trim().replace(/^#/, '');
        return list.filter(trx => {
            const idStr = String(trx.id || '');
            const paddedId = idStr.padStart(4, '0');
            const inv = (trx.invoice_code || '').toLowerCase();
            const store = (trx.store_name || '').toLowerCase();
            return idStr.includes(q) || paddedId.includes(q) || inv.includes(q) || store.includes(q);
        });
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
            <p class="font-black text-[#0f1419]">Prosedur Verifikasi QRIS:</p>
            <p class="text-[#536471] font-medium leading-relaxed">
                Cocokkan <strong>Nomor Antrean</strong> dan pastikan pembayaran QRIS telah masuk ke rekening panitia. Klik <strong>'Setujui'</strong> untuk menyelesaikan pesanan.
            </p>
        </div>
    </div>

    <!-- Live Search Bar: Filter by Queue Number, Store Name, or Invoice -->
    <div x-show="$store.app.transactions.filter(t => t.status === 'pending_verification').length > 0" x-cloak class="mb-6 bg-white p-3.5 rounded-3xl border border-[#eff3f4] shadow-xs">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#536471]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input 
                type="text" 
                x-model="searchQuery" 
                placeholder="Cari Nomor Antrean (misal: 1, 0001), nama warung, atau invoice..." 
                class="w-full pl-9 pr-16 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-full text-xs sm:text-sm text-[#0f1419] placeholder-[#536471] focus:ring-2 focus:ring-[#1d9bf0] focus:bg-white focus:outline-none font-semibold transition-all"
            >
            <button 
                x-show="searchQuery" 
                x-cloak
                @click="searchQuery = ''" 
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-xs font-black text-[#536471] hover:text-[#f4212e] cursor-pointer"
            >
                Reset
            </button>
        </div>
    </div>

    <!-- Verification Queue Cards List (Compact & Clear Item List Grid) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3.5 sm:gap-4">
        <template x-for="trx in filteredPending" :key="trx.id">
            <div class="bg-white rounded-2xl sm:rounded-3xl border border-[#eff3f4] p-4 sm:p-5 hover:border-[#bde2f9] hover:shadow-md transition-all flex flex-col justify-between shadow-2xs group relative">
                <div class="space-y-3">
                    <!-- Prominent Queue Number & Card Header -->
                    <div class="flex items-start justify-between pb-2.5 border-b border-[#eff3f4] gap-2">
                        <div class="flex items-center gap-2 min-w-0 flex-1">
                            <div class="px-2.5 py-1 rounded-xl bg-[#1d9bf0]/10 border border-[#1d9bf0]/20 text-[#1d9bf0] font-black text-sm tracking-wider shrink-0 shadow-2xs" x-text="`#${String(trx.id || 0).padStart(4, '0')}`">
                            </div>
                            <div class="min-w-0">
                                <span class="text-xs font-black text-[#0f1419] block truncate" x-text="trx.store_name"></span>
                                <div class="flex items-center gap-1 mt-0.5 text-[10px] text-[#536471] font-semibold truncate">
                                    <span x-text="trx.cashier_name || 'Kasir'"></span>
                                    <span>&bull;</span>
                                    <span class="truncate" x-text="trx.invoice_code"></span>
                                </div>
                            </div>
                        </div>
                        <span class="text-[10px] text-[#536471] font-bold shrink-0 bg-[#f7f9f9] px-2 py-0.5 rounded-full border border-[#eff3f4]" x-text="formatDateTime(trx.created_at).split(' ')[1] || formatDateTime(trx.created_at)"></span>
                    </div>

                    <!-- Total Tagihan Box -->
                    <div class="flex items-center justify-between p-2.5 bg-[#f0f8fe] rounded-xl border border-[#bde2f9]">
                        <span class="text-xs font-bold text-[#536471]">Total Tagihan:</span>
                        <span class="text-base font-black text-[#1d9bf0] tracking-tight" x-text="formatRupiah(trx.total_amount)"></span>
                    </div>

                    <!-- Clearly Visible Item List (Scroll if > 3 items) -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-wider text-[#536471]">
                            <span>Daftar Item:</span>
                            <span x-text="`${trx.items?.length || 0} menu`"></span>
                        </div>
                        <div class="space-y-1.5 max-h-[90px] overflow-y-auto custom-scrollbar pr-1 bg-[#f7f9f9] p-2.5 rounded-xl border border-[#eff3f4]">
                            <template x-for="item in trx.items" :key="item.product_id">
                                <div class="flex items-center justify-between text-xs py-0.5">
                                    <span class="font-bold text-[#0f1419] truncate pr-2" x-text="`${item.qty}x ${item.title}`"></span>
                                    <span class="font-semibold text-[#536471] shrink-0" x-text="formatRupiah(item.subtotal)"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Button: Lihat Bukti Transfer / Pembayaran -->
                    <button 
                        @click="openZoom(trx.payment_proof || trx.proof_image)"
                        type="button"
                        class="w-full py-2 px-3 rounded-xl bg-white hover:bg-[#e8f5fd] text-[#0f1419] hover:text-[#1d9bf0] border border-[#eff3f4] hover:border-[#bde2f9] text-xs font-black flex items-center justify-center gap-2 transition-all cursor-pointer shadow-2xs group/btn active:scale-95"
                    >
                        <svg class="w-4 h-4 text-[#1d9bf0]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span>Lihat Bukti Pembayaran</span>
                    </button>
                </div>

                <!-- Action Buttons: Reject & Approve -->
                <div class="pt-3 border-t border-[#eff3f4] flex gap-2 mt-3.5">
                    <!-- Reject Button -->
                    <button 
                        @click="$store.app.openRejectModal(trx)"
                        class="flex-1 py-2 px-3 rounded-full bg-[#eff3f4] hover:bg-rose-50 text-[#0f1419] hover:text-[#f4212e] text-xs font-bold transition-all border border-[#eff3f4] flex items-center justify-center gap-1.5 cursor-pointer active:scale-95"
                    >
                        <svg class="w-3.5 h-3.5 text-[#f4212e]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        <span>Tolak</span>
                    </button>

                    <!-- Approve Button -->
                    <button 
                        @click="$store.app.approveQris(trx.id)"
                        class="flex-1 py-2 px-3 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs font-black shadow-xs transition-all flex items-center justify-center gap-1.5 active:scale-95 cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
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

    @if(!empty($historyTransactions) && count($historyTransactions) > 0)
    <!-- History Section (Only shown if history exists) -->
    <div class="mt-8 mb-4">
        <h3 class="text-lg font-black text-[#0f1419]">Riwayat Verifikasi QRIS Terbaru</h3>
    </div>
    
    <div class="bg-white rounded-3xl border border-[#eff3f4] overflow-hidden shadow-xs mb-8">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[600px]">
                <thead>
                    <tr class="bg-[#f7f9f9] border-b border-[#eff3f4]">
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-[#536471]">No. Antrean</th>
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-[#536471]">Waktu</th>
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-[#536471]">Invoice</th>
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-[#536471]">Tenant</th>
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-[#536471] text-right">Total</th>
                        <th class="px-5 py-3 text-[10px] font-black uppercase tracking-wider text-[#536471] text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eff3f4]">
                    @foreach($historyTransactions as $history)
                        <tr class="hover:bg-[#f7f9f9] transition-colors">
                            <td class="px-5 py-3 text-xs font-black text-[#1d9bf0]">#{{ str_pad($history->id, 4, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-5 py-3 text-xs text-[#536471] font-semibold">{{ $history->updated_at->format('d M, H:i') }}</td>
                            <td class="px-5 py-3 text-xs font-black text-[#0f1419]">{{ $history->invoice_code }}</td>
                            <td class="px-5 py-3 text-xs text-[#536471] font-semibold">{{ $history->store->name ?? '-' }}</td>
                            <td class="px-5 py-3 text-xs font-black text-[#1d9bf0] text-right">Rp {{ number_format($history->total_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-center">
                                @if($history->status === 'paid')
                                    <span class="inline-block px-2.5 py-1 rounded-lg bg-[#e6f8f2] text-[#00ba7c] text-[10px] font-black border border-[#a6e9d5]">
                                        DISETUJUI
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-1 rounded-lg bg-[#fef2f2] text-[#f4212e] text-[10px] font-black border border-[#fecdd3]">
                                        DITOLAK
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

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
