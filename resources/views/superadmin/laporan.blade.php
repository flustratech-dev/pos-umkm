@extends('layouts.app')

@section('title', 'Laporan Fee Super Admin Multi-Event')

@section('content')
<div x-data="{
    selectedEventId: 'all',

    get reportTransactions() {
        return $store.app.transactions.filter(t => {
            return t.status === 'paid';
        });
    },

    get totalSuperAdminFee() {
        return this.reportTransactions.length * 1000;
    },

    get totalPlatformGross() {
        return this.reportTransactions.reduce((sum, t) => sum + t.total_amount, 0);
    }
}" class="space-y-6">

    <!-- Header (Twitter UI) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3.5 py-0.5 rounded-full bg-[#e8f5fd] text-[#1d9bf0] text-[10px] font-black uppercase border border-[#bde2f9]">Audit Finansial Platform</span>
                <span class="text-xs text-[#0f1419] font-semibold">Flat Fee Rp1.000 / Tx</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-[#0f1419] tracking-tight mt-1">Laporan Fee Super Admin</h2>
            <p class="text-xs sm:text-sm text-[#0f1419] font-medium mt-0.5">Rekapitulasi pendapatan lisensi sistem JADISATU berbasis potongan tetap Rp1.000 per transaksi paid</p>
        </div>

        <!-- Export Action Buttons (PDF, Word, Excel) -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- PDF Export Button -->
            <button 
                @click="$store.app.printSuperAdminReport(reportTransactions)"
                type="button" 
                class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-full bg-[#0f1419] hover:bg-[#272c30] text-white text-xs font-black shadow-xs transition-all cursor-pointer active:scale-95"
                title="Cetak Rekap atau Simpan PDF (Hitam Putih)"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>PDF / Cetak</span>
            </button>

            <!-- Word Export Button -->
            <button 
                @click="$store.app.exportSuperAdminReportWord(reportTransactions)"
                type="button" 
                class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs font-black shadow-xs transition-all cursor-pointer active:scale-95"
                title="Unduh Dokumen Word (.doc) Lengkap dengan TTD"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Word (.doc)</span>
            </button>

            <!-- Excel Export Button -->
            <button 
                @click="$store.app.exportSuperAdminReportExcel(reportTransactions)"
                type="button" 
                class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-full bg-[#00ba7c] hover:bg-[#00a36d] text-white text-xs font-black shadow-xs transition-all cursor-pointer active:scale-95"
                title="Unduh Rekap Spreadsheet Excel (.xls)"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Excel (.xls)</span>
            </button>
        </div>
    </div>

    <!-- KPI Metric Cards (Twitter Blue Accents & Crisp Black Font) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-[#1d9bf0] to-[#1271b3] rounded-3xl p-6 text-white shadow-lg shadow-[#1d9bf0]/25">
            <span class="text-xs font-bold text-white/90 uppercase tracking-wider block">Akumulasi Fee Super Admin</span>
            <h3 class="text-2xl sm:text-3xl font-black mt-2 tracking-tight text-white" x-text="formatRupiah(totalSuperAdminFee)"></h3>
            <p class="text-xs text-white/90 mt-2 font-medium">Rp1.000 flat × <span class="font-black text-white" x-text="reportTransactions.length"></span> transaksi paid</p>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-[#eff3f4] shadow-xs flex flex-col justify-between">
            <div>
                <span class="text-xs font-bold text-[#0f1419] uppercase tracking-wider block">Total Volume Transaksi Paid</span>
                <h3 class="text-xl font-black text-[#0f1419] mt-2" x-text="formatRupiah(totalPlatformGross)"></h3>
            </div>
            <p class="text-xs text-[#536471] mt-3 font-semibold">Gross volume seluruh tenant</p>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-[#eff3f4] shadow-xs flex flex-col justify-between">
            <div>
                <span class="text-xs font-bold text-[#0f1419] uppercase tracking-wider block">Total Event Terintegrasi</span>
                <h3 class="text-xl font-black text-[#1d9bf0] mt-2" x-text="$store.app.events.length + ' Event'"></h3>
            </div>
            <p class="text-xs text-[#536471] mt-3 font-semibold">Data tersimpan permanen</p>
        </div>
    </div>

    <!-- Transactions Audit Table (Twitter UI) -->
    <div class="bg-white rounded-3xl border border-[#eff3f4] shadow-xs overflow-hidden">
        <div class="p-5 border-b border-[#eff3f4] flex items-center justify-between">
            <h3 class="font-black text-base text-[#0f1419]">Rincian Transaksi Paid & Potongan Rp1.000</h3>
            <span class="text-xs text-[#536471]">Menampilkan seluruh transaksi Paid valid</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[#0f1419]">
                <thead class="bg-[#f7f9f9] border-b border-[#eff3f4] text-[10px] uppercase font-black text-[#0f1419] tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5">Invoice</th>
                        <th class="px-4 py-3.5">Waktu Paid</th>
                        <th class="px-4 py-3.5">Stand Tenant</th>
                        <th class="px-4 py-3.5">Metode</th>
                        <th class="px-4 py-3.5">Gross Volume</th>
                        <th class="px-4 py-3.5 text-[#1d9bf0] font-black">Fee Superadmin</th>
                        <th class="px-4 py-3.5 text-[#0f1419] font-black">Net EO</th>
                        <th class="px-4 py-3.5 text-[#1d9bf0] font-black">Hak Warung (75%)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eff3f4] font-medium">
                    <template x-for="tx in reportTransactions" :key="tx.id">
                        <tr class="hover:bg-[#f7f9f9] transition-colors">
                            <td class="px-4 py-3 font-black text-[#0f1419]" x-text="tx.invoice_code"></td>
                            <td class="px-4 py-3 text-[#536471] font-semibold" x-text="formatDateTime(tx.paid_at)"></td>
                            <td class="px-4 py-3 font-black text-[#0f1419]" x-text="tx.store_name"></td>
                            <td class="px-4 py-3 uppercase font-black text-[10px] text-[#1d9bf0]" x-text="tx.payment_method"></td>
                            <td class="px-4 py-3 font-black text-[#0f1419]" x-text="formatRupiah(tx.total_amount)"></td>
                            <td class="px-4 py-3 font-black text-[#1d9bf0] bg-[#f7f9f9]" x-text="formatRupiah(1000)"></td>
                            <td class="px-4 py-3 font-black text-[#0f1419]" x-text="formatRupiah(tx.revenue_split?.admin_net_share || (tx.total_amount * 0.25) - 1000)"></td>
                            <td class="px-4 py-3 font-black text-[#1d9bf0]" x-text="formatRupiah(tx.revenue_split?.owner_share || tx.total_amount * 0.75)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
