@extends('layouts.app')

@section('title', 'Laporan Penjualan Warung')

@section('content')
<div x-data="{
    searchInvoice: '',
    selectedStatus: 'all',
    selectedMethod: 'all',
    proofModalOpen: false,
    selectedProofUrl: '',

    get myTransactions() {
        const store = this.$store?.app?.getCurrentStore?.();
        const storeId = store ? store.id : null;
        const txs = this.$store?.app?.transactions || [];
        const q = (this.searchInvoice || '').toLowerCase().trim().replace(/^#/, '');
        return txs.filter(t => {
            const idStr = String(t.id || '');
            const paddedId = idStr.padStart(4, '0');
            const matchesStore = storeId ? (t.store_id == storeId) : true;
            const matchesSearch = !q || 
                                  (t.invoice_code || '').toLowerCase().includes(q) ||
                                  idStr.includes(q) ||
                                  paddedId.includes(q);
            const matchesStatus = this.selectedStatus === 'all' || t.status === this.selectedStatus;
            const matchesMethod = this.selectedMethod === 'all' || t.payment_method === this.selectedMethod;
            return matchesStore && matchesSearch && matchesStatus && matchesMethod;
        });
    },

    get stats() {
        const store = this.$store?.app?.getCurrentStore?.();
        return this.$store?.app?.getUserReportStats?.(store ? store.id : null) || {
            totalGross: 0,
            netIncome: 0,
            totalCount: 0,
            cancelledCount: 0,
            pendingCount: 0
        };
    }
}" class="space-y-6">

    <!-- Header & Export Action Buttons (Twitter UI) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-[#0f1419] tracking-tight">Laporan & Riwayat Penjualan</h2>
            <p class="text-xs sm:text-sm text-[#536471] font-semibold mt-0.5" x-text="`Rekapitulasi transaksi ${$store.app.getCurrentStore()?.name || 'Stand Saya'}`"></p>
        </div>

        <!-- Export Action Buttons (PDF, Word, Excel) -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- PDF Export Button -->
            <button 
                @click="$store.app.printUserReport(myTransactions)"
                type="button" 
                class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-full bg-[#0f1419] hover:bg-[#272c30] text-white text-xs font-black shadow-xs transition-all cursor-pointer active:scale-95"
                title="Cetak Dokumen atau Simpan PDF (Hitam Putih)"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>PDF / Cetak</span>
            </button>

            <!-- Word Export Button -->
            <button 
                @click="$store.app.exportUserReportWord(myTransactions)"
                type="button" 
                class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs font-black shadow-xs transition-all cursor-pointer active:scale-95"
                title="Unduh Dokumen Word (.doc) Lengkap dengan TTD"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Word (.doc)</span>
            </button>

            <!-- Excel Export Button -->
            <button 
                @click="$store.app.exportUserReportExcel(myTransactions)"
                type="button" 
                class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-full bg-[#00ba7c] hover:bg-[#00a36d] text-white text-xs font-black shadow-xs transition-all cursor-pointer active:scale-95"
                title="Unduh Rekap Spreadsheet Excel (.xls)"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Excel (.xls)</span>
            </button>
        </div>
    </div>

    <!-- Revenue Summary Cards (Twitter UI Style) -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        <!-- Net Share (Twitter Blue Gradient) -->
        <div class="bg-gradient-to-br from-[#1d9bf0] to-[#1271b3] rounded-3xl p-5 text-white shadow-lg shadow-[#1d9bf0]/25 col-span-2 sm:col-span-1">
            <span class="text-xs font-bold text-white/90 uppercase tracking-wider block">Pendapatan Bersih</span>
            <h3 class="text-xl sm:text-2xl font-black mt-1 tracking-tight text-white" x-text="formatRupiah(stats.netIncome)"></h3>
            <p class="text-[11px] text-white/90 mt-2 font-medium">Dari transaksi berstatus Paid</p>
        </div>

        <!-- Gross Volume -->
        <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs">
            <span class="text-xs font-bold text-[#0f1419] uppercase tracking-wider block">Total Omzet Bruto</span>
            <h3 class="text-lg sm:text-xl font-black text-[#0f1419] mt-1" x-text="formatRupiah(stats.totalGross)"></h3>
            <p class="text-[11px] text-[#536471] mt-2 font-semibold"><span class="font-black text-[#0f1419]" x-text="stats.totalCount"></span> transaksi sukses</p>
        </div>

        <!-- Potongan EO (25%) -->
        <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs">
            <span class="text-xs font-bold text-[#0f1419] uppercase tracking-wider block">Potongan EO (25%)</span>
            <h3 class="text-lg sm:text-xl font-black text-[#0f1419] mt-1" x-text="formatRupiah(stats.totalGross * 0.25)"></h3>
            <p class="text-[11px] text-[#536471] mt-2 font-semibold">Porsi bagian pihak EO</p>
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
                placeholder="Cari No. Antrean (misal: 0001) atau nomor invoice..." 
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
                <option value="pending">Pending Cash</option>                <option value="pending_verification">Pending QRIS</option>
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

            <!-- Filter Periode (menyatu dengan baris filter yang sudah ada) -->
            <div
                x-data="{
                    from: new URLSearchParams(location.search).get('from') || '',
                    to: new URLSearchParams(location.search).get('to') || '',
                    terapkan() {
                        const url = new URL(location.href);
                        if (this.from && this.to) {
                            url.searchParams.set('from', this.from);
                            url.searchParams.set('to', this.to);
                        } else if (this.from) {
                            url.searchParams.set('from', this.from);
                            url.searchParams.set('to', this.from);
                        } else {
                            url.searchParams.delete('from');
                            url.searchParams.delete('to');
                        }
                        location.href = url.toString();
                    },
                    cepat(hari) {
                        const d = new Date();
                        const akhir = new Date(d);
                        const awal = new Date(d);
                        if (hari === 'kemarin') { awal.setDate(awal.getDate() - 1); akhir.setDate(akhir.getDate() - 1); }
                        if (hari === '7hari') { awal.setDate(awal.getDate() - 6); }
                        const f = (x) => x.toISOString().substring(0, 10);
                        this.from = f(awal); this.to = f(akhir); this.terapkan();
                    }
                }"
                class="flex items-center gap-2 shrink-0"
            >
                <input
                    type="date"
                    x-model="from"
                    @change="if (to) terapkan()"
                    class="px-3 py-2 bg-[#f7f9f9] border border-[#eff3f4] rounded-full text-xs font-black text-[#0f1419] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none cursor-pointer"
                    title="Dari tanggal"
                >
                <span class="text-[10px] font-black text-[#536471]">s/d</span>
                <input
                    type="date"
                    x-model="to"
                    @change="terapkan()"
                    class="px-3 py-2 bg-[#f7f9f9] border border-[#eff3f4] rounded-full text-xs font-black text-[#0f1419] focus:ring-2 focus:ring-[#1d9bf0] focus:outline-none cursor-pointer"
                    title="Sampai tanggal"
                >
                <button @click="cepat('hariini')" type="button" class="px-3 py-2 bg-[#f7f9f9] hover:bg-[#e8f5fd] hover:text-[#1d9bf0] border border-[#eff3f4] rounded-full text-xs font-black text-[#0f1419] transition-colors cursor-pointer shrink-0">Hari Ini</button>
                <button @click="cepat('kemarin')" type="button" class="px-3 py-2 bg-[#f7f9f9] hover:bg-[#e8f5fd] hover:text-[#1d9bf0] border border-[#eff3f4] rounded-full text-xs font-black text-[#0f1419] transition-colors cursor-pointer shrink-0">Kemarin</button>
                <button @click="cepat('7hari')" type="button" class="px-3 py-2 bg-[#f7f9f9] hover:bg-[#e8f5fd] hover:text-[#1d9bf0] border border-[#eff3f4] rounded-full text-xs font-black text-[#0f1419] transition-colors cursor-pointer shrink-0">7 Hari</button>
                <button
                    x-show="from || to"
                    x-cloak
                    @click="from = ''; to = ''; terapkan()"
                    type="button"
                    class="px-3 py-2 bg-[#f4212e]/10 hover:bg-[#f4212e]/20 text-[#f4212e] rounded-full text-xs font-black transition-colors cursor-pointer shrink-0"
                    title="Tampilkan semua periode"
                >Reset</button>
            </div>
        </div>
    </div>

    <!-- DESKTOP TABLE VIEW (Twitter UI) -->
    <div class="hidden lg:block bg-white rounded-3xl border border-[#eff3f4] overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[#0f1419]">
                <thead class="bg-[#f7f9f9] border-b border-[#eff3f4] text-[10px] uppercase font-black text-[#0f1419] tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5">Invoice / Antrean</th>
                        <th class="px-4 py-3.5">Waktu</th>
                        <th class="px-4 py-3.5">Metode</th>
                        <th class="px-4 py-3.5">Total Belanja</th>
                        <th class="px-4 py-3.5">Potongan EO (25%)</th>
                        <th class="px-4 py-3.5">Uang Diterima (Cash)</th>
                        <th class="px-4 py-3.5">Kembalian (Cash)</th>
                        <th class="px-4 py-3.5 text-[#1d9bf0]">Hak Warung (75%)</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eff3f4] font-medium">
                    <template x-for="tx in myTransactions" :key="tx.id">
                        <tr class="hover:bg-[#f7f9f9] transition-colors">
                            <td class="px-4 py-3 font-black text-[#0f1419]">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-lg bg-[#e8f5fd] text-[#1d9bf0] text-[10px] font-black shrink-0" x-text="`#${String(tx.id || 0).padStart(4, '0')}`"></span>
                                    <span class="truncate" x-text="tx.invoice_code"></span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-[#536471] font-semibold" x-text="formatDateTime(tx.paid_at || tx.created_at)"></td>
                            <td class="px-4 py-3">
                                <span 
                                    class="px-3 py-1 rounded-full font-black uppercase text-[10px]"
                                    :class="tx.payment_method === 'cash' ? 'bg-[#e8f5fd] text-[#1d9bf0] border border-[#bde2f9]' : 'bg-[#f0f8fe] text-[#1d9bf0] border border-[#bde2f9]'"
                                    x-text="tx.payment_method"
                                ></span>
                            </td>
                            <td class="px-4 py-3 font-black text-[#0f1419]" x-text="tx.is_without_payment || (tx.status === 'rejected' && tx.rejection_reason === 'Tanpa Pembayaran') ? '-' : formatRupiah(tx.total_amount)"></td>
                            <td class="px-4 py-3 font-black text-[#0f1419]" x-text="tx.status === 'paid' ? formatRupiah(tx.revenue_split?.admin_gross_share || tx.total_amount * 0.25) : '-'"></td>
                            <td class="px-4 py-3 text-[#0f1419] font-bold" x-text="tx.is_without_payment || tx.status !== 'paid' ? '-' : (tx.payment_method === 'cash' ? formatRupiah(tx.amount_paid) : '-')"></td>
                            <td class="px-4 py-3 text-[#1d9bf0] font-black" x-text="tx.is_without_payment || tx.status !== 'paid' ? '-' : (tx.payment_method === 'cash' ? formatRupiah(tx.change_due) : '-')"></td>
                            <td class="px-4 py-3 font-black text-[#1d9bf0]" x-text="tx.status === 'paid' ? formatRupiah(tx.revenue_split?.owner_share || tx.total_amount * 0.75) : '-'"></td>
                            <td class="px-4 py-3">
                                <span 
                                    class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-[10px] font-bold"
                                    :class="{
                                        'bg-[#e8f5fd] text-[#1d9bf0] border border-[#bde2f9]': tx.status === 'paid',
                                        'bg-amber-50 text-amber-700 border border-amber-200': tx.is_without_payment || (tx.status === 'rejected' && tx.rejection_reason === 'Tanpa Pembayaran'),
                                        'bg-amber-50 text-[#ff7a00] border border-amber-200': !tx.is_without_payment && (tx.status === 'pending_verification' || tx.status === 'pending'),
                                        'bg-rose-50 text-[#f4212e] border border-rose-200': tx.status === 'rejected' && !tx.is_without_payment && tx.rejection_reason !== 'Tanpa Pembayaran',
                                        'bg-slate-100 text-slate-500 line-through': tx.status === 'cancelled'
                                    }"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full" :class="{
                                        'bg-[#1d9bf0]': tx.status === 'paid',
                                        'bg-amber-500': tx.is_without_payment || (tx.status === 'rejected' && tx.rejection_reason === 'Tanpa Pembayaran'),
                                        'bg-[#ff7a00]': !tx.is_without_payment && (tx.status === 'pending_verification' || tx.status === 'pending'),
                                        'bg-[#f4212e]': tx.status === 'rejected' && !tx.is_without_payment && tx.rejection_reason !== 'Tanpa Pembayaran',
                                        'bg-slate-400': tx.status === 'cancelled'
                                    }"></span>
                                    <span x-text="tx.is_without_payment || (tx.status === 'rejected' && tx.rejection_reason === 'Tanpa Pembayaran') ? 'Tanpa Pembayaran' : (tx.status === 'pending_verification' ? 'Pending Verif' : (tx.status === 'pending' ? 'Belum Bayar' : tx.status))"></span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <template x-if="tx.is_without_payment || (tx.status === 'rejected' && tx.rejection_reason === 'Tanpa Pembayaran')">
                                        <span class="px-2 py-0.5 bg-amber-50 text-amber-700 text-[9px] font-black rounded-full border border-amber-200">
                                            Tanpa Pembayaran
                                        </span>
                                    </template>
                                    <!-- QRIS lunas tanpa bukti (bukti gagal diunggah saat transaksi) -->
                                    <template x-if="tx.is_proof_missing">
                                        <span
                                            class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[9px] font-black uppercase tracking-wider border border-amber-200 cursor-help"
                                            :title="tx.proof_failure_reason || 'Bukti transfer gagal diunggah saat transaksi.'"
                                        >Tanpa Bukti</span>
                                    </template>
                                    <template x-if="tx.payment_method === 'qris' && (tx.proof_image || tx.payment_proof)">
                                        <button 
                                            @click="selectedProofUrl = tx.proof_image || tx.payment_proof; proofModalOpen = true"
                                            type="button" 
                                            class="p-2 text-[#1d9bf0] hover:bg-[#e8f5fd] rounded-full transition-colors cursor-pointer"
                                            title="Lihat Bukti Transfer"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </button>
                                    </template>
                                    <button 
                                        @click="$store.app.openReceipt(tx)"
                                        type="button" 
                                        class="p-2 text-[#1d9bf0] hover:bg-[#e8f5fd] rounded-full transition-colors cursor-pointer"
                                        title="Lihat Struk"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MOBILE CARD LIST VIEW (< lg) (2-Column Grid Kanan-Kiri) -->
    <div class="lg:hidden grid grid-cols-2 gap-2.5 sm:gap-3.5">
        <template x-for="tx in myTransactions" :key="tx.id">
            <div class="bg-white rounded-2xl border border-[#eff3f4] p-3 sm:p-4 shadow-xs flex flex-col justify-between space-y-2.5 hover:border-[#bde2f9] transition-all">
                <div class="space-y-2">
                    <div class="flex items-start justify-between gap-1">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1 mb-0.5">
                                <span class="px-1.5 py-0.5 rounded-md bg-[#e8f5fd] text-[#1d9bf0] text-[9px] font-black shrink-0" x-text="`#${String(tx.id || 0).padStart(4, '0')}`"></span>
                                <span class="font-black text-[11px] sm:text-xs text-[#0f1419] truncate block" x-text="tx.invoice_code"></span>
                            </div>
                            <span class="text-[9px] sm:text-[10px] text-[#536471] block font-medium truncate" x-text="formatDateTime(tx.paid_at || tx.created_at)"></span>
                        </div>

                        <span 
                            class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold shrink-0"
                            :class="{
                                'bg-[#e8f5fd] text-[#1d9bf0] border border-[#bde2f9]': tx.status === 'paid',
                                'bg-amber-50 text-amber-700 border border-amber-200': tx.is_without_payment || (tx.status === 'rejected' && tx.rejection_reason === 'Tanpa Pembayaran'),
                                'bg-amber-50 text-[#ff7a00] border border-amber-200': !tx.is_without_payment && (tx.status === 'pending_verification' || tx.status === 'pending'),
                                'bg-rose-50 text-[#f4212e] border border-rose-200': tx.status === 'rejected' && !tx.is_without_payment && tx.rejection_reason !== 'Tanpa Pembayaran',
                                'bg-slate-100 text-slate-500': tx.status === 'cancelled'
                            }"
                            x-text="tx.is_without_payment || (tx.status === 'rejected' && tx.rejection_reason === 'Tanpa Pembayaran') ? 'Tanpa Pembayaran' : (tx.status === 'pending_verification' ? 'Pending Verif' : (tx.status === 'pending' ? 'Belum Bayar' : tx.status))"
                        ></span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 text-xs py-1.5 sm:py-2 border-y border-[#eff3f4]">
                        <div>
                            <span class="text-[9px] sm:text-[10px] text-[#536471] block font-semibold">Total Transaksi</span>
                            <span class="font-black text-[11px] sm:text-xs text-[#0f1419] truncate block" x-text="tx.is_without_payment || (tx.status === 'rejected' && tx.rejection_reason === 'Tanpa Pembayaran') ? '-' : formatRupiah(tx.total_amount)"></span>
                        </div>
                        <div>
                            <span class="text-[9px] sm:text-[10px] text-[#536471] block font-semibold">Potongan EO (25%)</span>
                            <span class="font-black text-[11px] sm:text-xs text-[#0f1419] truncate block" x-text="tx.status === 'paid' ? formatRupiah(tx.revenue_split?.admin_gross_share || tx.total_amount * 0.25) : '-'"></span>
                        </div>
                        <div>
                            <span class="text-[9px] sm:text-[10px] text-[#536471] block font-semibold">Metode Bayar</span>
                            <span class="font-black uppercase text-[10px] sm:text-[11px] text-[#1d9bf0]" x-text="tx.payment_method"></span>
                        </div>

                        <template x-if="tx.payment_method === 'cash'">
                            <div>
                                <span class="text-[9px] sm:text-[10px] text-[#536471] block font-semibold">Uang Diterima</span>
                                <span class="font-black text-[11px] sm:text-xs text-[#0f1419] truncate block" x-text="tx.is_without_payment ? '-' : formatRupiah(tx.amount_paid)"></span>
                            </div>
                        </template>
                        <template x-if="tx.payment_method === 'cash'">
                            <div>
                                <span class="text-[9px] sm:text-[10px] text-[#536471] block font-semibold">Kembalian</span>
                                <span class="font-black text-[11px] sm:text-xs text-[#1d9bf0] truncate block" x-text="tx.is_without_payment ? '-' : formatRupiah(tx.change_due)"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 pt-1">
                    <div class="min-w-0">
                        <span class="text-[9px] sm:text-[10px] text-[#536471] font-semibold block">Hak Warung (75%):</span>
                        <span class="text-[11px] sm:text-xs font-black text-[#1d9bf0] truncate block" x-text="tx.status === 'paid' ? formatRupiah(tx.revenue_split?.owner_share || tx.total_amount * 0.75) : '-'"></span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-1.5">
                        <template x-if="tx.is_without_payment || (tx.status === 'rejected' && tx.rejection_reason === 'Tanpa Pembayaran')">
                            <span class="px-2 py-0.5 bg-amber-50 text-amber-700 text-[9px] font-black rounded-full border border-amber-200">
                                Tanpa Pembayaran
                            </span>
                        </template>
                        <template x-if="tx.payment_method === 'qris' && (tx.proof_image || tx.payment_proof)">
                            <button 
                                @click="selectedProofUrl = tx.proof_image || tx.payment_proof; proofModalOpen = true"
                                type="button" 
                                class="px-2.5 py-1 bg-white hover:bg-[#f7f9f9] text-[#1d9bf0] border border-[#bde2f9] font-black text-[10px] sm:text-xs rounded-full transition-colors cursor-pointer shadow-xs text-center"
                            >
                                Bukti
                            </button>
                        </template>
                        <button 
                            @click="$store.app.openReceipt(tx)"
                            type="button" 
                            class="w-full sm:w-auto px-3 sm:px-4 py-1 bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white font-black text-[10px] sm:text-xs rounded-full transition-colors cursor-pointer shadow-xs text-center"
                        >
                            Struk
                        </button>
                    </div>
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

    <!-- Modal Preview Bukti Pembayaran QRIS -->
    <div 
        x-show="proofModalOpen" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
        x-cloak
    >
        <div 
            x-show="proofModalOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.away="proofModalOpen = false"
            class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl border border-[#eff3f4] space-y-4"
        >
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-[#0f1419]">Bukti Transfer QRIS</h3>
                <button @click="proofModalOpen = false" class="text-[#0f1419] hover:text-[#1d9bf0] p-1.5 rounded-full hover:bg-[#eff3f4] cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="rounded-2xl overflow-hidden bg-[#f7f9f9] border border-[#eff3f4] flex items-center justify-center min-h-[200px] max-h-[60vh]">
                <img :src="selectedProofUrl" alt="Bukti Transfer QRIS" class="w-full h-auto max-h-[60vh] object-contain">
            </div>
            <button 
                @click="proofModalOpen = false"
                class="w-full py-2.5 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white font-black text-xs transition-all shadow-md shadow-[#1d9bf0]/25 cursor-pointer"
            >
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection
