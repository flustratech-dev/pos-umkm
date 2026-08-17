@extends('layouts.app')

@section('title', 'Laporan Lengkap & Bagi Hasil EO')

@section('content')
<div x-data="{
    searchQuery: '',
    selectedStoreId: 'all',
    selectedStatus: 'all',
    selectedMethod: 'all',

    get filteredTransactions() {
        const txs = this.$store?.app?.transactions || [];
        return txs.filter(t => {
            const matchesSearch = (t.invoice_code || '').toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                                  (t.store_name || '').toLowerCase().includes(this.searchQuery.toLowerCase());
            const matchesStore = this.selectedStoreId === 'all' || t.store_id == this.selectedStoreId;
            const matchesStatus = this.selectedStatus === 'all' || t.status === this.selectedStatus;
            const matchesMethod = this.selectedMethod === 'all' || t.payment_method === this.selectedMethod;
            return matchesSearch && matchesStore && matchesStatus && matchesMethod;
        });
    },

    get stats() {
        return this.$store?.app?.getAdminReportStats?.() || {
            totalGross: 0,
            ownerTotal: 0,
            adminGross: 0,
            adminNet: 0,
            superadminTotal: 0,
            paidCount: 0,
            cashCount: 0,
            qrisCount: 0
        };
    },

    proofModalOpen: false,
    selectedProofUrl: ''
}" class="space-y-6">

    <!-- Header (Twitter UI) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-[#0f1419] tracking-tight">Laporan Full & Pembagian Hasil</h2>
            <p class="text-xs sm:text-sm text-[#0f1419] font-medium mt-0.5" x-text="`Rekapitulasi seluruh tenant ${$store.app.getActiveEvent()?.name}`"></p>
        </div>

        <!-- Export Action Buttons (PDF, Word, Excel) -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- PDF Export Button -->
            <button 
                @click="$store.app.printAdminReport(filteredTransactions)"
                type="button" 
                class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-full bg-[#0f1419] hover:bg-[#272c30] text-white text-xs font-black shadow-xs transition-all cursor-pointer active:scale-95"
                title="Cetak Dokumen atau Simpan PDF Resmi (Hitam Putih)"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>PDF / Cetak</span>
            </button>

            <!-- Word Export Button -->
            <button 
                @click="$store.app.exportAdminReportWord(filteredTransactions)"
                type="button" 
                class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs font-black shadow-xs transition-all cursor-pointer active:scale-95"
                title="Unduh Dokumen Word (.doc) Lengkap dengan Kolom TTD"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Word (.doc)</span>
            </button>

            <!-- Excel Export Button -->
            <button 
                @click="$store.app.exportAdminReportExcel(filteredTransactions)"
                type="button" 
                class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-full bg-[#00ba7c] hover:bg-[#00a36d] text-white text-xs font-black shadow-xs transition-all cursor-pointer active:scale-95"
                title="Unduh Rekap Spreadsheet Excel (.xls)"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Excel (.xls)</span>
            </button>
        </div>
    </div>

    <!-- 4-Tier Revenue Summary Cards (Twitter Blue Accents & Crisp Black Font) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- 1. Gross Volume -->
        <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs">
            <span class="text-xs font-bold text-[#0f1419] uppercase tracking-wider block">Total Omzet Bruto</span>
            <h3 class="text-xl font-black text-[#0f1419] mt-1" x-text="formatRupiah(stats.totalGross)"></h3>
            <p class="text-[11px] text-[#536471] mt-2 font-semibold"><span class="font-black text-[#0f1419]" x-text="stats.paidCount"></span> transaksi paid</p>
        </div>

        <!-- 2. Hak Warung (75%) -->
        <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs">
            <span class="text-xs font-bold text-[#0f1419] uppercase tracking-wider block">Porsi Warung (75%)</span>
            <h3 class="text-xl font-black text-[#1d9bf0] mt-1" x-text="formatRupiah(stats.ownerTotal)"></h3>
            <p class="text-[11px] text-[#536471] mt-2 font-medium">Hak seluruh pemilik stand</p>
        </div>

        <!-- 3. Admin Net (Twitter Blue Gradient) -->
        <div class="bg-gradient-to-br from-[#1d9bf0] to-[#1271b3] rounded-3xl p-5 text-white shadow-lg shadow-[#1d9bf0]/25">
            <span class="text-xs font-bold text-white/90 uppercase tracking-wider block">Pendapatan Bersih EO</span>
            <h3 class="text-xl font-black mt-1 text-white" x-text="formatRupiah(stats.adminNet)"></h3>
            <p class="text-[11px] text-white/90 mt-2 font-medium">Net 22.5% dari Omzet</p>
        </div>

        <!-- 4. Developer Platform Fee -->
        <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs">
            <span class="text-xs font-bold text-[#0f1419] uppercase tracking-wider block">Fee Developer</span>
            <h3 class="text-xl font-black text-[#0f1419] mt-1" x-text="formatRupiah(stats.superadminTotal)"></h3>
            <p class="text-[11px] text-[#536471] mt-2 font-medium">2.5% dari Omzet Paid</p>
        </div>
    </div>

    <!-- SETTLEMENT / SERAH-TERIMA SECTION -->
    <div class="bg-white rounded-3xl border border-[#eff3f4] shadow-xs overflow-hidden">
        <div class="p-5 pb-4 border-b border-[#eff3f4]">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-black text-[#0f1419]">Rekap Settlement per Warung</h3>
                    <p class="text-[11px] text-[#536471] font-medium">Berapa yang harus admin transfer ke tiap warung setelah offset Cash vs QRIS</p>
                </div>
            </div>

            <!-- Global Settlement Summary -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 mt-4">
                <div class="bg-[#f7f9f9] rounded-2xl p-3 text-center">
                    <span class="text-[10px] font-bold text-[#536471] uppercase block">💵 Total Cash</span>
                    <span class="text-sm font-black text-[#0f1419] mt-0.5 block" x-text="formatRupiah(stats.totalCash)"></span>
                    <span class="text-[10px] text-[#536471] font-medium block">Dipegang warung</span>
                </div>
                <div class="bg-[#f7f9f9] rounded-2xl p-3 text-center">
                    <span class="text-[10px] font-bold text-[#536471] uppercase block">📱 Total QRIS</span>
                    <span class="text-sm font-black text-[#1d9bf0] mt-0.5 block" x-text="formatRupiah(stats.totalQris)"></span>
                    <span class="text-[10px] text-[#536471] font-medium block">Dipegang admin</span>
                </div>
                <div class="bg-[#f7f9f9] rounded-2xl p-3 text-center">
                    <span class="text-[10px] font-bold text-[#536471] uppercase block">Hak Admin di Cash</span>
                    <span class="text-sm font-black text-[#0f1419] mt-0.5 block" x-text="formatRupiah(stats.cashHakAdmin)"></span>
                    <span class="text-[10px] text-[#536471] font-medium block">22.5% dari cash + platform fee</span>
                </div>
                <div class="rounded-2xl p-3 text-center" :class="stats.netSettlement >= 0 ? 'bg-emerald-50 border border-emerald-200' : 'bg-amber-50 border border-amber-200'">
                    <span class="text-[10px] font-bold uppercase block" :class="stats.netSettlement >= 0 ? 'text-emerald-600' : 'text-amber-600'" x-text="stats.netSettlement >= 0 ? '🔄 Admin → Warung' : '🔄 Warung → Admin'"></span>
                    <span class="text-sm font-black mt-0.5 block" :class="stats.netSettlement >= 0 ? 'text-emerald-700' : 'text-amber-700'" x-text="formatRupiah(Math.abs(stats.netSettlement))"></span>
                    <span class="text-[10px] font-medium block" :class="stats.netSettlement >= 0 ? 'text-emerald-500' : 'text-amber-500'" x-text="stats.netSettlement >= 0 ? 'Total transfer ke warung' : 'Total warung setor ke admin'"></span>
                </div>
            </div>
        <!-- Per-Store Settlement Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[#0f1419]">
                <thead class="bg-[#f7f9f9] border-b border-[#eff3f4] text-[10px] uppercase font-black text-[#0f1419] tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Warung</th>
                        <th class="px-4 py-3 text-right">Omzet</th>
                        <th class="px-4 py-3 text-right">💵 Cash</th>
                        <th class="px-4 py-3 text-right">📱 QRIS</th>
                        <th class="px-4 py-3 text-right">Hak Warung (75%)</th>
                        <th class="px-4 py-3 text-right">Hak EO (22.5%)</th>
                        <th class="px-4 py-3 text-center">Serah Terima</th>
                        <th class="px-4 py-3 text-center">Cetak PDF</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eff3f4] font-medium">
                    <template x-for="s in $store.app.getSettlementPerStore()" :key="s.store_id">
                        <tr class="hover:bg-[#f7f9f9] transition-colors">
                            <td class="px-4 py-3">
                                <span class="font-black text-[#0f1419]" x-text="s.store_name"></span>
                                <span class="text-[10px] text-[#536471] block" x-text="`${s.txCount} transaksi`"></span>
                            </td>
                            <td class="px-4 py-3 text-right font-black" x-text="formatRupiah(s.totalGross)"></td>
                            <td class="px-4 py-3 text-right" x-text="formatRupiah(s.totalCash)"></td>
                            <td class="px-4 py-3 text-right text-[#1d9bf0] font-bold" x-text="formatRupiah(s.totalQris)"></td>
                            <td class="px-4 py-3 text-right text-[#1d9bf0] font-black" x-text="formatRupiah(s.hakWarung)"></td>
                            <td class="px-4 py-3 text-right font-black" x-text="formatRupiah(s.hakAdmin)"></td>
                            <td class="px-4 py-3 text-center">
                                <div x-show="s.netSettlement > 0" x-cloak class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200">
                                    <span class="text-[10px] font-bold text-emerald-600">Admin bayar</span>
                                    <span class="text-[10px] font-black text-emerald-700" x-text="formatRupiah(s.netSettlement)"></span>
                                </div>
                                <div x-show="s.netSettlement < 0" x-cloak class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 border border-amber-200">
                                    <span class="text-[10px] font-bold text-amber-600">Warung setor</span>
                                    <span class="text-[10px] font-black text-amber-700" x-text="formatRupiah(Math.abs(s.netSettlement))"></span>
                                </div>
                                <div x-show="s.netSettlement === 0" x-cloak class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100">
                                    <span class="text-[10px] font-bold text-slate-500">Lunas</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button 
                                    @click="$store.app.printTenantReport(s.store_id)"
                                    type="button"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-[#0f1419] hover:bg-[#272c30] text-white text-[10px] font-black shadow-2xs transition-all active:scale-95 cursor-pointer"
                                    title="Cetak PDF Rekap Warung Ini"
                                >
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    <span>PDF Stand</span>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <template x-if="$store.app.getSettlementPerStore().length === 0">
            <div class="p-8 text-center">
                <p class="text-xs text-[#536471] font-semibold">Belum ada transaksi paid untuk dihitung</p>
            </div>
        </template>
    </div>

    <!-- Filter Bar (Twitter UI) -->
    <div class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between bg-white p-3.5 rounded-2xl border border-[#eff3f4] shadow-xs">
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#536471]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input 
                type="text" 
                x-model="searchQuery" 
                placeholder="Cari invoice atau nama warung..." 
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

            <!-- Dynamic PDF Button when a specific store is selected -->
            <button 
                x-show="selectedStoreId !== 'all'"
                x-transition
                @click="$store.app.printTenantReport(selectedStoreId)"
                type="button"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full bg-[#0f1419] hover:bg-[#272c30] text-white text-xs font-black shadow-xs transition-all active:scale-95 cursor-pointer shrink-0" 
                title="Cetak PDF untuk Warung yang Dipilih"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak PDF Stand Ini</span>
            </button>

            <!-- Filter Metode -->
            <select 
                x-model="selectedMethod" 
                class="px-4 py-2 bg-[#f7f9f9] border border-[#eff3f4] rounded-full text-xs font-black text-[#0f1419] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none cursor-pointer"
            >
                <option value="all">Semua Metode</option>
                <option value="cash">💵 Cash</option>
                <option value="qris">📱 QRIS</option>
            </select>

            <!-- Filter Status -->
            <select 
                x-model="selectedStatus" 
                class="px-4 py-2 bg-[#f7f9f9] border border-[#eff3f4] rounded-full text-xs font-black text-[#0f1419] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none cursor-pointer"
            >
                <option value="all">Semua Status</option>
                <option value="paid">Paid (Sukses)</option>
                <option value="pending">Pending Cash</option>
                <option value="pending_verification">Pending QRIS</option>
                <option value="cancelled">Cancelled (Dibatalkan)</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </div>

    <!-- DESKTOP COMPREHENSIVE TABLE (Twitter UI) -->
    <div class="hidden lg:block bg-white rounded-3xl border border-[#eff3f4] overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[#0f1419]">
                <thead class="bg-[#f7f9f9] border-b border-[#eff3f4] text-[10px] uppercase font-black text-[#0f1419] tracking-wider">
                    <tr>
                        <th class="px-3.5 py-3.5">Invoice</th>
                        <th class="px-3.5 py-3.5">Warung / Stand</th>
                        <th class="px-3.5 py-3.5">Metode</th>
                        <th class="px-3.5 py-3.5">Total Belanja</th>
                        <th class="px-3.5 py-3.5 text-[#1d9bf0]">Warung (75%)</th>
                        <th class="px-3.5 py-3.5 text-[#1d9bf0]">Total Potongan (25%)</th>
                        <th class="px-3.5 py-3.5">Fee Developer</th>
                        <th class="px-3.5 py-3.5 text-[#0f1419] font-black">EO Net</th>
                        <th class="px-3.5 py-3.5">Status</th>
                        <th class="px-3.5 py-3.5 text-center">Aksi / Cancel</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eff3f4] font-medium">
                    <template x-for="tx in filteredTransactions" :key="tx.id">
                        <tr class="hover:bg-[#f7f9f9] transition-colors">
                            <td class="px-3.5 py-3 font-black text-[#0f1419]">
                                <span x-text="tx.invoice_code"></span>
                                <span class="text-[10px] text-[#536471] block font-normal" x-text="formatDateTime(tx.paid_at || tx.created_at)"></span>
                            </td>
                            <td class="px-3.5 py-3 font-black text-[#0f1419]" x-text="tx.store_name"></td>
                            <td class="px-3.5 py-3">
                                <span 
                                    class="px-3 py-1 rounded-full font-black uppercase text-[10px]"
                                    :class="tx.payment_method === 'cash' ? 'bg-[#e8f5fd] text-[#1d9bf0] border border-[#bde2f9]' : 'bg-[#f0f8fe] text-[#1d9bf0] border border-[#bde2f9]'"
                                    x-text="tx.payment_method"
                                ></span>
                            </td>
                            <td class="px-3.5 py-3 font-black text-[#0f1419]" x-text="formatRupiah(tx.total_amount)"></td>
                            <td class="px-3.5 py-3 font-black text-[#1d9bf0]" x-text="tx.status === 'paid' ? formatRupiah(tx.revenue_split?.owner_share || tx.total_amount * 0.75) : '-'"></td>
                            <td class="px-3.5 py-3 font-black text-[#1d9bf0]" x-text="tx.status === 'paid' ? formatRupiah(tx.revenue_split?.admin_gross_share || tx.total_amount * 0.25) : '-'"></td>
                            <td class="px-3.5 py-3 font-black text-[#0f1419]" x-text="tx.status === 'paid' ? formatRupiah(tx.revenue_split?.superadmin_share || (tx.total_amount * 0.025)) : '-'"></td>
                            <td class="px-3.5 py-3 font-black text-[#0f1419] bg-[#f7f9f9]" x-text="tx.status === 'paid' ? formatRupiah(tx.revenue_split?.admin_net_share || (tx.total_amount * 0.225)) : '-'"></td>
                            <td class="px-3.5 py-3">
                                <span 
                                    class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-[10px] font-bold"
                                    :class="{
                                        'bg-[#e8f5fd] text-[#1d9bf0] border border-[#bde2f9]': tx.status === 'paid',
                                        'bg-amber-50 text-[#ff7a00] border border-amber-200': tx.status === 'pending_verification' || tx.status === 'pending',
                                        'bg-rose-50 text-[#f4212e] border border-rose-200': tx.status === 'rejected',
                                        'bg-slate-100 text-slate-500 line-through': tx.status === 'cancelled'
                                    }"
                                >
                                    <span x-text="tx.status === 'pending_verification' ? 'Pending Verif' : (tx.status === 'pending' ? 'Belum Bayar' : tx.status)"></span>
                                </span>
                            </td>
                            <td class="px-3.5 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- QRIS Proof Button -->
                                    <template x-if="tx.payment_method === 'qris' && (tx.proof_image || tx.payment_proof)">
                                        <button 
                                            @click="selectedProofUrl = tx.proof_image || tx.payment_proof; proofModalOpen = true"
                                            type="button" 
                                            class="p-2 text-[#1d9bf0] hover:bg-[#e8f5fd] rounded-full transition-colors cursor-pointer"
                                            title="Lihat Bukti QRIS"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </button>
                                    </template>

                                    <!-- Struk Button -->
                                    <button 
                                        @click="$store.app.openReceipt(tx)"
                                        type="button" 
                                        class="p-2 text-[#1d9bf0] hover:bg-[#e8f5fd] rounded-full transition-colors cursor-pointer"
                                        title="Lihat Struk"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </button>

                                    <!-- CANCEL TRANSACTION BUTTON (Twitter Pill) -->
                                    <template x-if="tx.status === 'paid'">
                                        <button 
                                            @click="$store.app.openCancelTransactionModal(tx)"
                                            type="button" 
                                            class="px-3 py-1 bg-rose-50 hover:bg-[#f4212e] text-[#f4212e] hover:text-white text-[10px] font-black rounded-full transition-colors cursor-pointer"
                                            title="Batalkan Transaksi Paid"
                                        >
                                            Batalkan
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MOBILE CARD LIST (< lg, 2-Column Grid Kanan-Kiri) -->
    <div class="lg:hidden grid grid-cols-2 gap-2.5 sm:gap-3.5">
        <template x-for="tx in filteredTransactions" :key="tx.id">
            <div class="bg-white rounded-2xl border border-[#eff3f4] p-3 sm:p-4 shadow-xs flex flex-col justify-between space-y-2.5 hover:border-[#bde2f9] transition-all">
                <div class="space-y-2">
                    <div class="flex items-start justify-between gap-1">
                        <div class="min-w-0 flex-1">
                            <span class="font-black text-[11px] sm:text-xs text-[#0f1419] truncate block" x-text="tx.invoice_code"></span>
                            <span class="text-[9px] sm:text-[10px] text-[#536471] block font-medium truncate" x-text="`${tx.store_name} • ${formatDateTime(tx.created_at)}`"></span>
                        </div>

                        <span 
                            class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold shrink-0"
                            :class="{
                                'bg-[#e8f5fd] text-[#1d9bf0] border border-[#bde2f9]': tx.status === 'paid',
                                'bg-amber-50 text-[#ff7a00] border border-amber-200': tx.status === 'pending_verification' || tx.status === 'pending',
                                'bg-rose-50 text-[#f4212e] border border-rose-200': tx.status === 'rejected',
                                'bg-slate-100 text-slate-500': tx.status === 'cancelled'
                            }"
                            x-text="tx.status === 'pending_verification' ? 'Pending Verif' : (tx.status === 'pending' ? 'Belum Bayar' : tx.status)"
                        ></span>
                    </div>

                    <!-- Breakdown Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 text-xs py-1.5 sm:py-2 border-y border-[#eff3f4]">
                        <div>
                            <span class="text-[9px] sm:text-[10px] text-[#536471] block font-semibold">Total Omzet</span>
                            <span class="font-black text-[11px] sm:text-xs text-[#0f1419] truncate block" x-text="formatRupiah(tx.total_amount)"></span>
                        </div>
                        <div>
                            <span class="text-[9px] sm:text-[10px] text-[#536471] block font-semibold">Metode</span>
                            <span class="font-black uppercase text-[10px] sm:text-[11px] text-[#1d9bf0]" x-text="tx.payment_method"></span>
                        </div>
                        <div>
                            <span class="text-[9px] sm:text-[10px] text-[#536471] block font-semibold">Hak Warung (75%)</span>
                            <span class="font-black text-[11px] sm:text-xs text-[#1d9bf0] truncate block" x-text="tx.status === 'paid' ? formatRupiah(tx.revenue_split?.owner_share || tx.total_amount * 0.75) : '-'"></span>
                        </div>
                        <div>
                            <span class="text-[9px] sm:text-[10px] text-[#536471] block font-semibold">Net EO</span>
                            <span class="font-black text-[11px] sm:text-xs text-[#0f1419] truncate block" x-text="tx.status === 'paid' ? formatRupiah(tx.revenue_split?.admin_net_share || (tx.total_amount * 0.225)) : '-'"></span>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex flex-wrap items-center justify-between gap-1.5 pt-1">
                    <div class="flex gap-1.5">
                        <button 
                            @click="$store.app.openReceipt(tx)"
                            class="px-3 sm:px-4 py-1 bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-[10px] sm:text-xs font-black rounded-full shadow-xs cursor-pointer"
                        >
                            Struk
                        </button>
                        <template x-if="tx.payment_method === 'qris' && (tx.proof_image || tx.payment_proof)">
                            <button 
                                @click="selectedProofUrl = tx.proof_image || tx.payment_proof; proofModalOpen = true"
                                class="px-2.5 sm:px-4 py-1 bg-[#e8f5fd] text-[#1d9bf0] hover:bg-[#1d9bf0] hover:text-white text-[10px] sm:text-xs font-black rounded-full transition-colors cursor-pointer"
                            >
                                Bukti
                            </button>
                        </template>
                    </div>

                    <template x-if="tx.status === 'paid'">
                        <button 
                            @click="$store.app.openCancelTransactionModal(tx)"
                            type="button" 
                            class="px-2.5 sm:px-3.5 py-1 bg-rose-50 hover:bg-[#f4212e] text-[#f4212e] hover:text-white text-[10px] sm:text-xs font-black rounded-full transition-colors cursor-pointer"
                        >
                            Batalkan
                        </button>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <template x-if="filteredTransactions.length === 0">
        <div class="bg-white rounded-3xl border border-[#eff3f4] p-12 text-center max-w-md mx-auto my-6 shadow-2xs">
            <div class="w-16 h-16 bg-[#e8f5fd] rounded-full text-[#1d9bf0] flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <h4 class="text-sm font-black text-[#0f1419]">Belum Ada Data Transaksi</h4>
            <p class="text-xs text-[#536471] font-semibold mt-1">Transaksi penjualan yang dilakukan kasir stand akan tercatat dan dihitung otomatis di sini.</p>
        </div>
    </template>

    <!-- CANCEL TRANSACTION MODAL WITH MANDATORY REASON & CHECKBOX (SLIDE UP BOTTOM SHEET ON MOBILE) -->
    <div 
        x-show="$store.app.cancelModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <!-- Backdrop -->
        <div 
            x-show="$store.app.cancelModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-[#0f1419]/60 backdrop-blur-xs transition-opacity" 
            @click="$store.app.cancelModalOpen = false"
        ></div>

        <!-- Position: Bottom on Mobile (`items-end p-0`), Center on Desktop (`sm:items-center sm:p-4`) -->
        <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4 text-left">
            <div 
                x-show="$store.app.cancelModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                class="relative w-full max-w-lg bg-white rounded-t-3xl sm:rounded-3xl p-6 sm:p-8 shadow-2xl space-y-4 border-t sm:border border-[#eff3f4] text-left max-h-[92vh] sm:max-h-none overflow-y-auto custom-scrollbar"
            >
                <!-- Mobile Drag / Pull Indicator Handle -->
                <div class="w-12 h-1.5 bg-[#cfd9de] rounded-full mx-auto mb-2 sm:hidden"></div>

                <!-- Modal Title -->
                <div class="flex items-center justify-between pb-3 border-b border-[#eff3f4]">
                    <div class="flex items-center gap-2 text-[#f4212e]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <h3 class="text-base sm:text-lg font-black text-[#0f1419]">Batalkan Transaksi Paid</h3>
                    </div>
                    <button @click="$store.app.cancelModalOpen = false" class="text-[#0f1419] hover:text-[#1d9bf0] p-1.5 rounded-full hover:bg-[#eff3f4] cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Transaction Target Info -->
                <div class="p-3.5 bg-[#f7f9f9] rounded-2xl border border-[#eff3f4] text-xs space-y-1">
                    <div class="flex justify-between">
                        <span class="text-[#536471] font-semibold">Invoice:</span>
                        <span class="font-black text-[#0f1419]" x-text="$store.app.transactionToCancel?.invoice_code"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#536471] font-semibold">Stand Warung:</span>
                        <span class="font-black text-[#0f1419]" x-text="$store.app.transactionToCancel?.store_name"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#536471] font-semibold">Total Nominal:</span>
                        <span class="font-black text-[#f4212e]" x-text="formatRupiah($store.app.transactionToCancel?.total_amount)"></span>
                    </div>
                </div>

                <!-- Form Fields -->
                <div class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-bold text-[#0f1419] mb-1">Alasan Pembatalan (Pilihan Cepat)</label>
                        <select 
                            x-model="$store.app.cancelReasonCategory"
                            class="w-full px-4 py-2.5 bg-[#f7f9f9] border border-[#eff3f4] rounded-xl text-xs text-[#0f1419] focus:ring-2 focus:ring-[#f4212e] focus:outline-none font-semibold cursor-pointer"
                        >
                            <option value="Salah input barang/harga">Salah input barang/harga</option>
                            <option value="Barang dikembalikan customer">Barang dikembalikan customer</option>
                            <option value="Kesalahan sistem">Kesalahan sistem</option>
                            <option value="Lainnya (isi manual)">Lainnya (isi manual)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#0f1419] mb-1">
                            Catatan Tambahan
                            <span x-show="$store.app.cancelReasonCategory === 'Lainnya (isi manual)'" x-cloak class="text-[#f4212e] font-bold">*Wajib</span>
                        </label>
                        <textarea 
                            x-model="$store.app.cancelCustomNote"
                            rows="2"
                            placeholder="Ketik keterangan detail alasan pembatalan..."
                            class="w-full px-4 py-2 bg-[#f7f9f9] border border-[#eff3f4] rounded-2xl text-xs text-[#0f1419] focus:ring-2 focus:ring-[#f4212e] focus:outline-none font-medium"
                        ></textarea>
                    </div>

                    <!-- MANDATORY ACKNOWLEDGEMENT CHECKBOX -->
                    <div class="p-3.5 bg-rose-50/50 rounded-2xl border border-rose-100">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input 
                                type="checkbox" 
                                x-model="$store.app.cancelRefundConfirmed"
                                class="w-4 h-4 mt-0.5 rounded border-rose-300 text-[#f4212e] focus:ring-[#f4212e]"
                            >
                            <span class="text-xs text-[#0f1419] font-semibold leading-relaxed">
                                Saya konfirmasi bahwa pembatalan ini sudah dikoordinasikan dengan pemilik warung dan/atau refund ke customer (jika ada) sudah/akan ditangani secara manual di luar sistem.
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Submit Action (Twitter Pill Buttons) -->
                <div class="pt-2 flex gap-3">
                    <button 
                        type="button" 
                        @click="$store.app.cancelModalOpen = false"
                        class="flex-1 py-3 rounded-full bg-[#eff3f4] hover:bg-slate-200 text-[#0f1419] text-xs font-black transition-colors cursor-pointer"
                    >
                        Batal
                    </button>
                    <button 
                        type="button" 
                        @click="$store.app.confirmCancelTransaction()"
                        :disabled="!$store.app.cancelRefundConfirmed"
                        class="flex-1 py-3 rounded-full bg-[#f4212e] hover:bg-rose-700 text-white text-xs font-black shadow-md shadow-rose-600/30 transition-all disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer"
                    >
                        Batalkan Transaksi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- VIEW PROOF MODAL (SLIDE UP BOTTOM SHEET ON MOBILE) -->
    <div 
        x-show="proofModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <div 
            x-show="proofModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-[#0f1419]/80 backdrop-blur-md transition-opacity" 
            @click="proofModalOpen = false"
        ></div>

        <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4 text-center">
            <div 
                x-show="proofModalOpen"
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
                    <h4 class="text-xs font-black text-[#0f1419]">Bukti Transfer Transaksi</h4>
                    <button @click="proofModalOpen = false" class="text-[#0f1419] hover:text-[#1d9bf0] p-1.5 rounded-full hover:bg-[#eff3f4] cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="rounded-2xl overflow-hidden border border-[#eff3f4]">
                    <img :src="selectedProofUrl" class="w-full h-auto max-h-[60vh] object-contain mx-auto">
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
