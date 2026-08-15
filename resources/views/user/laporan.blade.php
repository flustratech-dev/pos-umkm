@extends('layouts.app')

@section('title', 'Laporan Penjualan Warung')

@section('content')
<div x-data="{
    searchInvoice: '',
    selectedStatus: 'all',
    selectedMethod: 'all',

    get myTransactions() {
        const storeId = $store.app.getCurrentStore()?.id || 1;
        return $store.app.transactions.filter(t => {
            const matchesStore = t.store_id === storeId;
            const matchesSearch = t.invoice_code.toLowerCase().includes(this.searchInvoice.toLowerCase());
            const matchesStatus = this.selectedStatus === 'all' || t.status === this.selectedStatus;
            const matchesMethod = this.selectedMethod === 'all' || t.payment_method === this.selectedMethod;
            return matchesStore && matchesSearch && matchesStatus && matchesMethod;
        });
    },

    get stats() {
        return $store.app.getUserReportStats($store.app.getCurrentStore()?.id || 1);
    }
}" class="space-y-6">

    <!-- Header & Print Action (Twitter Blue Button) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-[#0f1419] tracking-tight">Laporan & Riwayat Penjualan</h2>
            <p class="text-xs sm:text-sm text-[#0f1419] font-medium mt-0.5" x-text="`Rekapitulasi transaksi ${$store.app.getCurrentStore()?.name}`"></p>
        </div>

        <button 
            @click="window.print()"
            type="button" 
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs sm:text-sm font-black shadow-md shadow-[#1d9bf0]/25 transition-all cursor-pointer active:scale-95"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            <span>Cetak Rekap / PDF</span>
        </button>
    </div>

    <!-- Revenue Summary Cards (Twitter UI Style) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Net Share 75% (Twitter Blue Gradient) -->
        <div class="bg-gradient-to-br from-[#1d9bf0] to-[#1271b3] rounded-3xl p-5 text-white shadow-lg shadow-[#1d9bf0]/25 col-span-2 sm:col-span-1">
            <span class="text-xs font-bold text-white/90 uppercase tracking-wider block">Pendapatan Bersih (75%)</span>
            <h3 class="text-xl sm:text-2xl font-black mt-1 tracking-tight text-white" x-text="formatRupiah(stats.netIncome)"></h3>
            <p class="text-[11px] text-white/90 mt-2 font-medium">Porsi 75% dari transaksi berstatus Paid</p>
        </div>

        <!-- Gross Volume -->
        <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs">
            <span class="text-xs font-bold text-[#0f1419] uppercase tracking-wider block">Total Omzet Bruto</span>
            <h3 class="text-lg sm:text-xl font-black text-[#0f1419] mt-1" x-text="formatRupiah(stats.totalGross)"></h3>
            <p class="text-[11px] text-[#536471] mt-2 font-semibold"><span class="font-black text-[#0f1419]" x-text="stats.totalCount"></span> transaksi sukses</p>
        </div>

        <!-- Pending QRIS -->
        <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs">
            <span class="text-xs font-bold text-[#0f1419] uppercase tracking-wider block">Menunggu Verif EO</span>
            <h3 class="text-lg sm:text-xl font-black text-[#ff7a00] mt-1" x-text="stats.pendingCount"></h3>
            <p class="text-[11px] text-[#536471] mt-2 font-semibold">Sedang dicek panitia</p>
        </div>

        <!-- Cancelled -->
        <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs">
            <span class="text-xs font-bold text-[#0f1419] uppercase tracking-wider block">Dibatalkan / Refund</span>
            <h3 class="text-lg sm:text-xl font-black text-[#f4212e] mt-1" x-text="stats.cancelledCount"></h3>
            <p class="text-[11px] text-[#536471] mt-2 font-semibold">Tidak dihitung omzet</p>
        </div>
    </div>

    <!-- Filters & Search (Twitter UI) -->
    <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between bg-white p-3.5 rounded-2xl border border-[#eff3f4] shadow-xs">
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#536471]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input 
                type="text" 
                x-model="searchInvoice"
                placeholder="Cari nomor invoice (mis. INV/001)..." 
                class="w-full pl-9 pr-4 py-2 bg-[#f7f9f9] border border-[#eff3f4] rounded-full text-xs sm:text-sm text-[#0f1419] placeholder-[#536471] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none font-semibold"
            >
        </div>

        <!-- Filter Status Dropdowns -->
        <div class="flex items-center gap-2">
            <select 
                x-model="selectedStatus" 
                class="px-4 py-2 bg-[#f7f9f9] border border-[#eff3f4] rounded-full text-xs font-black text-[#0f1419] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none cursor-pointer"
            >
                <option value="all">Semua Status</option>
                <option value="paid">Paid (Sukses)</option>
                <option value="pending_verification">Pending QRIS</option>
                <option value="cancelled">Cancelled</option>
                <option value="rejected">Rejected</option>
            </select>

            <select 
                x-model="selectedMethod" 
                class="px-4 py-2 bg-[#f7f9f9] border border-[#eff3f4] rounded-full text-xs font-black text-[#0f1419] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none cursor-pointer"
            >
                <option value="all">Semua Metode</option>
                <option value="cash">Cash / Tunai</option>
                <option value="qris">QRIS</option>
            </select>
        </div>
    </div>

    <!-- DESKTOP TABLE VIEW (Twitter UI) -->
    <div class="hidden lg:block bg-white rounded-3xl border border-[#eff3f4] overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[#0f1419]">
                <thead class="bg-[#f7f9f9] border-b border-[#eff3f4] text-[10px] uppercase font-black text-[#0f1419] tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5">Invoice</th>
                        <th class="px-4 py-3.5">Waktu</th>
                        <th class="px-4 py-3.5">Metode</th>
                        <th class="px-4 py-3.5">Total Belanja</th>
                        <th class="px-4 py-3.5">Uang Diterima (Cash)</th>
                        <th class="px-4 py-3.5">Kembalian (Cash)</th>
                        <th class="px-4 py-3.5">Porsi Warung (75%)</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eff3f4] font-medium">
                    <template x-for="tx in myTransactions" :key="tx.id">
                        <tr class="hover:bg-[#f7f9f9] transition-colors">
                            <td class="px-4 py-3 font-black text-[#0f1419]" x-text="tx.invoice_code"></td>
                            <td class="px-4 py-3 text-[#536471] font-semibold" x-text="formatDateTime(tx.paid_at || tx.created_at)"></td>
                            <td class="px-4 py-3">
                                <span 
                                    class="px-3 py-1 rounded-full font-black uppercase text-[10px]"
                                    :class="tx.payment_method === 'cash' ? 'bg-[#e8f5fd] text-[#1d9bf0] border border-[#bde2f9]' : 'bg-[#f0f8fe] text-[#1d9bf0] border border-[#bde2f9]'"
                                    x-text="tx.payment_method"
                                ></span>
                            </td>
                            <td class="px-4 py-3 font-black text-[#0f1419]" x-text="formatRupiah(tx.total_amount)"></td>
                            <td class="px-4 py-3 text-[#0f1419] font-bold" x-text="tx.payment_method === 'cash' ? formatRupiah(tx.amount_paid) : '-'"></td>
                            <td class="px-4 py-3 text-[#1d9bf0] font-black" x-text="tx.payment_method === 'cash' ? formatRupiah(tx.change_due) : '-'"></td>
                            <td class="px-4 py-3 font-black text-[#1d9bf0]" x-text="tx.status === 'paid' ? formatRupiah(tx.revenue_split?.owner_share || tx.total_amount * 0.75) : '-'"></td>
                            <td class="px-4 py-3">
                                <span 
                                    class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-[10px] font-bold"
                                    :class="{
                                        'bg-[#e8f5fd] text-[#1d9bf0] border border-[#bde2f9]': tx.status === 'paid',
                                        'bg-amber-50 text-[#ff7a00] border border-amber-200': tx.status === 'pending_verification',
                                        'bg-rose-50 text-[#f4212e] border border-rose-200': tx.status === 'rejected',
                                        'bg-slate-100 text-slate-500 line-through': tx.status === 'cancelled'
                                    }"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full" :class="{
                                        'bg-[#1d9bf0]': tx.status === 'paid',
                                        'bg-[#ff7a00]': tx.status === 'pending_verification',
                                        'bg-[#f4212e]': tx.status === 'rejected',
                                        'bg-slate-400': tx.status === 'cancelled'
                                    }"></span>
                                    <span x-text="tx.status === 'pending_verification' ? 'Pending' : tx.status"></span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button 
                                    @click="$store.app.openReceipt(tx)"
                                    type="button" 
                                    class="p-2 text-[#1d9bf0] hover:bg-[#e8f5fd] rounded-full transition-colors cursor-pointer"
                                    title="Lihat Struk"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MOBILE CARD LIST VIEW (< lg) (Twitter UI) -->
    <div class="lg:hidden space-y-3">
        <template x-for="tx in myTransactions" :key="tx.id">
            <div class="bg-white rounded-2xl border border-[#eff3f4] p-4 shadow-xs space-y-2.5">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-black text-xs text-[#0f1419]" x-text="tx.invoice_code"></span>
                        <span class="text-[10px] text-[#536471] block font-medium" x-text="formatDateTime(tx.paid_at || tx.created_at)"></span>
                    </div>

                    <span 
                        class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                        :class="{
                            'bg-[#e8f5fd] text-[#1d9bf0] border border-[#bde2f9]': tx.status === 'paid',
                            'bg-amber-50 text-[#ff7a00] border border-amber-200': tx.status === 'pending_verification',
                            'bg-rose-50 text-[#f4212e] border border-rose-200': tx.status === 'rejected',
                            'bg-slate-100 text-slate-500': tx.status === 'cancelled'
                        }"
                        x-text="tx.status === 'pending_verification' ? 'Pending' : tx.status"
                    ></span>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs py-2 border-y border-[#eff3f4]">
                    <div>
                        <span class="text-[10px] text-[#536471] block font-semibold">Total Transaksi</span>
                        <span class="font-black text-[#0f1419]" x-text="formatRupiah(tx.total_amount)"></span>
                    </div>
                    <div>
                        <span class="text-[10px] text-[#536471] block font-semibold">Metode Bayar</span>
                        <span class="font-black uppercase text-[11px] text-[#1d9bf0]" x-text="tx.payment_method"></span>
                    </div>

                    <template x-if="tx.payment_method === 'cash'">
                        <div>
                            <span class="text-[10px] text-[#536471] block font-semibold">Uang Diterima</span>
                            <span class="font-black text-[#0f1419]" x-text="formatRupiah(tx.amount_paid)"></span>
                        </div>
                    </template>
                    <template x-if="tx.payment_method === 'cash'">
                        <div>
                            <span class="text-[10px] text-[#536471] block font-semibold">Kembalian</span>
                            <span class="font-black text-[#1d9bf0]" x-text="formatRupiah(tx.change_due)"></span>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <div>
                        <span class="text-[10px] text-[#536471] font-semibold">Bagian Warung (75%):</span>
                        <span class="text-xs font-black text-[#1d9bf0] ml-1" x-text="tx.status === 'paid' ? formatRupiah(tx.revenue_split?.owner_share || tx.total_amount * 0.75) : '-'"></span>
                    </div>

                    <!-- Twitter Blue Struk Button -->
                    <button 
                        @click="$store.app.openReceipt(tx)"
                        type="button" 
                        class="px-4 py-1 bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white font-black text-xs rounded-full transition-colors cursor-pointer shadow-xs"
                    >
                        Struk
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <template x-if="myTransactions.length === 0">
        <div class="bg-white rounded-3xl border border-[#eff3f4] p-12 text-center max-w-sm mx-auto">
            <p class="text-xs text-[#0f1419] font-bold">Tidak ada riwayat transaksi yang cocok dengan filter.</p>
        </div>
    </template>
</div>
@endsection
