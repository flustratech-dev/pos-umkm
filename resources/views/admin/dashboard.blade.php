@extends('layouts.app')

@section('title', 'Dashboard Admin EO')

@section('content')
<div x-data="{
    chartHourly: null,
    chartMethod: null,

    get adminStats() {
        return $store.app.getAdminReportStats();
    },

    initCharts() {
        this.$nextTick(() => {
            const ctxHourly = document.getElementById('hourlySalesChart');
            if (ctxHourly && window.Chart) {
                if (this.chartHourly) this.chartHourly.destroy();
                this.chartHourly = new window.Chart(ctxHourly, {
                    type: 'line',
                    data: {
                        labels: ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'],
                        datasets: [{
                            label: 'Omzet Penjualan (Rp)',
                            data: [85000, 140000, 310000, 480000, 390000, 220000, 180000, 0, 0],
                            borderColor: '#1d9bf0',
                            backgroundColor: 'rgba(29, 155, 240, 0.12)',
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#1d9bf0',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            borderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    color: '#0f1419',
                                    font: { weight: '600' },
                                    callback: (val) => 'Rp ' + (val / 1000) + 'k'
                                },
                                grid: { color: '#eff3f4' }
                            },
                            x: {
                                ticks: {
                                    color: '#0f1419',
                                    font: { weight: '600' }
                                },
                                grid: { color: '#eff3f4' }
                            }
                        }
                    }
                });
            }

            const ctxMethod = document.getElementById('methodDonutChart');
            if (ctxMethod && window.Chart) {
                if (this.chartMethod) this.chartMethod.destroy();
                const cashCount = $store.app.transactions.filter(t => t.payment_method === 'cash' && t.status === 'paid').length;
                const qrisCount = $store.app.transactions.filter(t => t.payment_method === 'qris' && t.status === 'paid').length;

                this.chartMethod = new window.Chart(ctxMethod, {
                    type: 'doughnut',
                    data: {
                        labels: ['Cash / Tunai', 'QRIS Statis'],
                        datasets: [{
                            data: [cashCount || 4, qrisCount || 3],
                            backgroundColor: ['#1d9bf0', '#71c9f8'],
                            borderColor: '#ffffff',
                            borderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { 
                                position: 'bottom',
                                labels: {
                                    color: '#0f1419',
                                    font: { weight: 'bold', size: 12 }
                                }
                            }
                        }
                    }
                });
            }
        });
    }
}" x-init="initCharts()" class="space-y-6">

    <!-- Header Section with Event Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3.5 py-1 rounded-full bg-[#e8f5fd] text-[#1d9bf0] text-xs font-black uppercase border border-[#bde2f9]">Panel Panitia EO</span>
                <span class="text-xs text-[#0f1419] font-semibold">Bazar UMKM Terintegrasi</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-[#0f1419] tracking-tight mt-1.5 flex items-center gap-1.5">
                <span x-text="$store.app.getActiveEvent()?.name"></span>
                <svg class="w-5 h-5 text-[#1d9bf0] shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.79-4-4-4-.495 0-.965.084-1.4.238C14.55 2.475 13.18 1.6 11.6 1.6c-1.58 0-2.95.875-3.6 2.148-.435-.154-.905-.238-1.4-.238-2.21 0-4 1.79-4 4 0 .495.084.965.238 1.4C1.575 9.55.7 10.92.7 12.5c0 1.58.875 2.95 2.148 3.6-.154.435-.238.905-.238 1.4 0 2.21 1.79 4 4 4 .495 0 .965-.084 1.4-.238.65 1.273 2.02 2.148 3.6 2.148 1.58 0 2.95-.875 3.6-2.148.435.154.905.238 1.4.238 2.21 0 4-1.79 4-4 0-.495-.084-.965-.238-1.4 1.273-.65 2.148-2.02 2.148-3.6zm-12.28 4.22l-4.22-4.22 1.414-1.414 2.806 2.806 6.806-6.806 1.414 1.414-8.22 8.22z"></path></svg>
            </h2>
        </div>

        <!-- Twitter Blue Action Button -->
        <a 
            href="/admin/verifikasi-qris" 
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs sm:text-sm font-black shadow-md shadow-[#1d9bf0]/25 transition-all active:scale-95"
        >
            <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
            <span>Antrean Verifikasi QRIS</span>
            <span class="px-2.5 py-0.5 rounded-full bg-white text-[#1d9bf0] text-xs font-black shadow-2xs" x-text="adminStats.pendingCount"></span>
        </a>
    </div>

    <!-- KPI Metric Cards (Twitter Blue Accent & Crisp Black Fonts) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Total Gross Revenue -->
        <div class="bg-gradient-to-br from-[#1d9bf0] to-[#1271b3] rounded-3xl p-5 text-white shadow-lg shadow-[#1d9bf0]/25 col-span-2 sm:col-span-1">
            <span class="text-xs font-bold text-white/90 uppercase tracking-wider block">Total Omzet Event</span>
            <h3 class="text-2xl font-black mt-1 tracking-tight text-white" x-text="formatRupiah(adminStats.totalGross)"></h3>
            <p class="text-[11px] text-white/90 mt-2"><span class="font-black text-white" x-text="adminStats.paidCount"></span> transaksi berhasil</p>
        </div>

        <!-- Net EO Revenue -->
        <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs">
            <span class="text-xs font-bold text-[#0f1419] uppercase tracking-wider block">Bagian Bersih EO</span>
            <h3 class="text-xl font-black text-[#1d9bf0] mt-1" x-text="formatRupiah(adminStats.adminNet)"></h3>
            <p class="text-[11px] text-[#536471] mt-2 font-medium">25% Gross - Rp1.000 platform</p>
        </div>

        <!-- Active Stores Count -->
        <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs">
            <span class="text-xs font-bold text-[#0f1419] uppercase tracking-wider block">Warung Terdaftar</span>
            <h3 class="text-xl font-black text-[#0f1419] mt-1" x-text="adminStats.storesCount"></h3>
            <p class="text-[11px] text-[#536471] mt-2 font-medium">Semua stand aktif berjualan</p>
        </div>

        <!-- Super Admin Flat Fee -->
        <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs">
            <span class="text-xs font-bold text-[#0f1419] uppercase tracking-wider block">Fee Super Admin</span>
            <h3 class="text-xl font-black text-[#1d9bf0] mt-1" x-text="formatRupiah(adminStats.superadminTotal)"></h3>
            <p class="text-[11px] text-[#536471] mt-2 font-medium">Rp1.000 flat per transaksi paid</p>
        </div>
    </div>

    <!-- Charts Section (Twitter Blue Palette Pie/Donut & Line Chart) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Sales Hourly Trend -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-5 sm:p-6 border border-[#eff3f4] shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-black text-base text-[#0f1419]">Tren Transaksi per Jam</h3>
                    <p class="text-xs text-[#536471]">Aktivitas omzet penjualan kasir hari ini</p>
                </div>
                <span class="text-xs font-black text-[#1d9bf0] bg-[#e8f5fd] px-3.5 py-1 rounded-full border border-[#bde2f9]">Live Hari Ini</span>
            </div>
            <div class="h-64 relative">
                <canvas id="hourlySalesChart"></canvas>
            </div>
        </div>

        <!-- Payment Method Distribution (Twitter Blue Shades Piechart) -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-[#eff3f4] shadow-xs space-y-3 flex flex-col justify-between">
            <div>
                <h3 class="font-black text-base text-[#0f1419]">Komposisi Pembayaran</h3>
                <p class="text-xs text-[#536471]">Perbandingan Cash vs QRIS</p>
            </div>
            <div class="h-56 relative my-auto">
                <canvas id="methodDonutChart"></canvas>
            </div>
            <div class="grid grid-cols-2 gap-2 text-center text-xs pt-2 border-t border-[#eff3f4]">
                <div class="p-2.5 bg-[#e8f5fd] rounded-2xl border border-[#bde2f9]">
                    <span class="text-[10px] text-[#1d9bf0] block font-bold">Tunai / Cash</span>
                    <span class="font-black text-[#0f1419] text-sm" x-text="$store.app.transactions.filter(t => t.payment_method === 'cash' && t.status === 'paid').length + ' Tx'"></span>
                </div>
                <div class="p-2.5 bg-[#f0f8fe] rounded-2xl border border-[#bde2f9]">
                    <span class="text-[10px] text-[#46b2f2] block font-bold">QRIS Statis</span>
                    <span class="font-black text-[#0f1419] text-sm" x-text="$store.app.transactions.filter(t => t.payment_method === 'qris' && t.status === 'paid').length + ' Tx'"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions Quick View -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-[#eff3f4] shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="font-black text-base text-[#0f1419]">Transaksi Terbaru Lintas Stand</h3>
            <a href="/admin/laporan" class="text-xs font-black text-[#1d9bf0] hover:underline">Lihat Semua Laporan &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[#0f1419]">
                <thead class="bg-[#f7f9f9] border-b border-[#eff3f4] text-[10px] uppercase font-black text-[#0f1419]">
                    <tr>
                        <th class="px-4 py-3">Invoice</th>
                        <th class="px-4 py-3">Stand Warung</th>
                        <th class="px-4 py-3">Nominal</th>
                        <th class="px-4 py-3">Metode</th>
                        <th class="px-4 py-3">Porsi Warung (75%)</th>
                        <th class="px-4 py-3">Net EO</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#eff3f4]">
                    <template x-for="tx in $store.app.transactions.slice(0, 5)" :key="tx.id">
                        <tr class="hover:bg-[#f7f9f9]">
                            <td class="px-4 py-3 font-black text-[#0f1419]" x-text="tx.invoice_code"></td>
                            <td class="px-4 py-3 text-[#0f1419] font-bold" x-text="tx.store_name"></td>
                            <td class="px-4 py-3 font-black text-[#0f1419]" x-text="formatRupiah(tx.total_amount)"></td>
                            <td class="px-4 py-3 uppercase font-black text-[10px] text-[#1d9bf0]" x-text="tx.payment_method"></td>
                            <td class="px-4 py-3 text-[#1d9bf0] font-black" x-text="tx.status === 'paid' ? formatRupiah(tx.revenue_split?.owner_share || tx.total_amount * 0.75) : '-'"></td>
                            <td class="px-4 py-3 text-[#0f1419] font-black" x-text="tx.status === 'paid' ? formatRupiah(tx.revenue_split?.admin_net_share || (tx.total_amount * 0.25) - 1000) : '-'"></td>
                            <td class="px-4 py-3">
                                <span 
                                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                    :class="{
                                        'bg-[#e8f5fd] text-[#1d9bf0] border border-[#bde2f9]': tx.status === 'paid',
                                        'bg-amber-50 text-[#ff7a00] border border-amber-200': tx.status === 'pending_verification',
                                        'bg-rose-50 text-[#f4212e] border border-rose-200': tx.status === 'rejected',
                                        'bg-slate-100 text-slate-500 line-through': tx.status === 'cancelled'
                                    }"
                                    x-text="tx.status === 'pending_verification' ? 'Pending' : tx.status"
                                ></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
