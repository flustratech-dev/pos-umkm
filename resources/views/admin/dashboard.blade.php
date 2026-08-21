@extends('layouts.app')

@section('title', 'Dashboard Admin EO')

@section('content')
<script>
window.__adminSalesChart = null;
window.__adminMethodChart = null;

window.__SALES_TREND__ = @json($salesTrend ?? []);
window.__EVENT_STATS__ = @json($stats ?? []);

async function renderAdminSalesChart(timeframe) {
    const ctxHourly = document.getElementById('hourlySalesChart');
    if (!ctxHourly) return;
    if (window.loadChartJs) await window.loadChartJs();
    if (!window.Chart) return;

    // Seri grafik dihitung di server dari seluruh transaksi event. Sebelumnya
    // dihitung dari daftar transaksi di browser yang hanya berisi 10 terakhir,
    // sehingga grafiknya selalu rata nol.
    const tren = (window.__SALES_TREND__ || {})[timeframe] || { labels: [], cash: [], qris: [] };
    const labels = tren.labels || [];
    const cashData = tren.cash || [];
    const qrisData = tren.qris || [];

    if (window.__adminSalesChart) {
        window.__adminSalesChart.destroy();
        window.__adminSalesChart = null;
    }

    window.__adminSalesChart = new window.Chart(ctxHourly, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Tunai / Cash',
                    data: cashData,
                    borderColor: '#00ba7c',
                    backgroundColor: 'rgba(0, 186, 124, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: '#00ba7c'
                },
                {
                    label: 'QRIS Statis',
                    data: qrisData,
                    borderColor: '#1d9bf0',
                    backgroundColor: 'rgba(29, 155, 240, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointBackgroundColor: '#1d9bf0'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8, font: { size: 11, weight: 'bold' }, color: '#0f1419' }
                },
                tooltip: {
                    callbacks: {
                        label: (item) => `${item.dataset.label}: ${window.formatRupiah ? window.formatRupiah(item.parsed.y) : item.parsed.y}`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#536471',
                        font: { size: 10 },
                        callback: (value) => window.formatRupiah ? window.formatRupiah(value) : value
                    },
                    grid: { color: '#eff3f4' }
                },
                x: {
                    ticks: { color: '#536471', font: { size: 10 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 12 },
                    grid: { display: false }
                }
            }
        }
    });
}

async function renderAdminMethodChart() {
    const ctxMethod = document.getElementById('methodDonutChart');
    if (!ctxMethod) return;
    if (window.loadChartJs) await window.loadChartJs();
    if (!window.Chart) return;
    if (window.__adminMethodChart) {
        window.__adminMethodChart.destroy();
        window.__adminMethodChart = null;
    }

    // Jumlah transaksi lunas untuk seluruh event, dihitung di server.
    const ringkasan = window.__EVENT_STATS__ || {};
    const cashCount = ringkasan.cash_count || 0;
    const qrisCount = ringkasan.qris_count || 0;

    window.__adminMethodChart = new window.Chart(ctxMethod, {
        type: 'doughnut',
        data: {
            labels: ['Cash / Tunai', 'QRIS Statis'],
            datasets: [{
                data: (cashCount === 0 && qrisCount === 0) ? [0, 0] : [cashCount, qrisCount],
                backgroundColor: ['#00ba7c', '#1d9bf0'],
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
</script>

<div x-data="{
    chartTimeframe: '1d',

    get adminStats() {
        return this.$store?.app?.getAdminReportStats?.() || {
            totalGross: 0,
            ownerTotal: 0,
            adminGross: 0,
            adminNet: 0,
            superAdminShare: 0,
            paidCount: 0,
            totalTx: 0,
            qrisPendingCount: 0
        };
    },

    get timeframeTitle() {
        if (this.chartTimeframe === '7d') return 'Tren Transaksi Cash vs QRIS (7 Hari)';
        if (this.chartTimeframe === '30d') return 'Tren Transaksi Cash vs QRIS (1 Bulan)';
        return 'Tren Transaksi Cash vs QRIS (Hari Ini)';
    },

    get timeframeSubtitle() {
        if (this.chartTimeframe === '7d') return 'Perbandingan omzet Tunai vs QRIS harian 7 hari terakhir';
        if (this.chartTimeframe === '30d') return 'Perbandingan omzet Tunai vs QRIS harian 30 hari terakhir';
        return 'Perbandingan omzet Tunai vs QRIS kasir per jam hari ini';
    },

    setTimeframe(tf) {
        this.chartTimeframe = tf;
        renderAdminSalesChart(tf);
    },

    initCharts() {
        this.$nextTick(() => {
            renderAdminSalesChart(this.chartTimeframe);
            renderAdminMethodChart();
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

        <!-- Twitter Blue Action Button (Only show when there is a pending queue) -->
        <a 
            x-show="{{ (int) ($stats['pending_count'] ?? 0) }} > 0"
            x-cloak
            href="/admin/verifikasi-cash" 
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-[#1d9bf0] hover:bg-[#1a8cd8] text-white text-xs sm:text-sm font-black shadow-md shadow-[#1d9bf0]/25 transition-all active:scale-95"
        >
            <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
            <span>Antrean Verifikasi Cash</span>
            <span class="px-2.5 py-0.5 rounded-full bg-white text-[#1d9bf0] text-xs font-black shadow-2xs">{{ number_format($stats['pending_count'] ?? 0, 0, ',', '.') }}</span>
        </a>
    </div>

    <!-- 1 Card Menu dengan 4 Kotak Icon (Mobile Only - Tepat di bawah Nama Event) -->
    <div class="lg:hidden bg-white rounded-3xl p-4 sm:p-5 border border-[#eff3f4] shadow-xs">
        <div class="grid grid-cols-4 gap-2 sm:gap-4 text-center">
            <!-- 1. Event -->
            <a 
                href="/admin/events" 
                class="flex flex-col items-center group cursor-pointer active:scale-95 transition-transform"
            >
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-[#e8f5fd] group-hover:bg-[#1d9bf0] text-[#1d9bf0] group-hover:text-white flex items-center justify-center transition-all shadow-2xs group-hover:shadow-md group-hover:shadow-[#1d9bf0]/25">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <span class="text-[11px] sm:text-xs font-black text-[#0f1419] group-hover:text-[#1d9bf0] mt-2 block tracking-tight truncate w-full">Event</span>
            </a>

            <!-- 2. Stand Warung -->
            <a 
                href="/admin/warung" 
                class="flex flex-col items-center group cursor-pointer active:scale-95 transition-transform"
            >
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-[#e8f5fd] group-hover:bg-[#1d9bf0] text-[#1d9bf0] group-hover:text-white flex items-center justify-center transition-all shadow-2xs group-hover:shadow-md group-hover:shadow-[#1d9bf0]/25">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <span class="text-[11px] sm:text-xs font-black text-[#0f1419] group-hover:text-[#1d9bf0] mt-2 block tracking-tight truncate w-full">Warung</span>
            </a>

            <!-- 3. Helpdesk -->
            <a 
                href="/admin/helpdesk" 
                class="flex flex-col items-center group cursor-pointer active:scale-95 transition-transform"
            >
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-[#e8f5fd] group-hover:bg-[#1d9bf0] text-[#1d9bf0] group-hover:text-white flex items-center justify-center transition-all shadow-2xs group-hover:shadow-md group-hover:shadow-[#1d9bf0]/25">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                </div>
                <span class="text-[11px] sm:text-xs font-black text-[#0f1419] group-hover:text-[#1d9bf0] mt-2 block tracking-tight truncate w-full">Helpdesk</span>
            </a>

            <!-- 4. SOP Kasir -->
            <a 
                href="/admin/panduan" 
                class="flex flex-col items-center group cursor-pointer active:scale-95 transition-transform"
            >
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-[#e8f5fd] group-hover:bg-[#1d9bf0] text-[#1d9bf0] group-hover:text-white flex items-center justify-center transition-all shadow-2xs group-hover:shadow-md group-hover:shadow-[#1d9bf0]/25">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <span class="text-[11px] sm:text-xs font-black text-[#0f1419] group-hover:text-[#1d9bf0] mt-2 block tracking-tight truncate w-full">SOP Kasir</span>
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards (Twitter Blue Accent & Crisp Black Fonts) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Total Gross Revenue -->
        <div class="bg-gradient-to-br from-[#1d9bf0] to-[#1271b3] rounded-3xl p-5 text-white shadow-lg shadow-[#1d9bf0]/25 col-span-2 sm:col-span-1">
            <span class="text-xs font-bold text-white/90 uppercase tracking-wider block">Total Omzet Event</span>
            <h3 class="text-2xl font-black mt-1 tracking-tight text-white">{{ 'Rp ' . number_format($stats['total_gross'] ?? 0, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-white/90 mt-2"><span class="font-black text-white">{{ number_format($stats['paid_count'] ?? 0, 0, ',', '.') }}</span> transaksi berhasil</p>
        </div>

        <!-- Net EO Revenue (25%) -->
        <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs">
            <span class="text-xs font-bold text-[#0f1419] uppercase tracking-wider block">Bagian EO (25%)</span>
            <h3 class="text-xl font-black text-[#1d9bf0] mt-1">{{ 'Rp ' . number_format($stats['admin_gross'] ?? 0, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-[#536471] mt-2 font-medium">Total 25% dari Omzet</p>
        </div>

        <!-- Active Stores Count -->
        <div class="bg-white rounded-3xl p-5 border border-[#eff3f4] shadow-xs">
            <span class="text-xs font-bold text-[#0f1419] uppercase tracking-wider block">Warung Terdaftar</span>
            <h3 class="text-xl font-black text-[#0f1419] mt-1">{{ number_format($stats['stores_count'] ?? 0, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-[#536471] mt-2 font-medium">Semua stand aktif berjualan</p>
        </div>

        <!-- Pending Cash Count -->
        <div class="bg-amber-50 rounded-3xl p-5 border border-amber-200 shadow-xs">
            <span class="text-xs font-bold text-[#ff7a00] uppercase tracking-wider block">Pending Cash</span>
            <h3 class="text-xl font-black text-[#ff7a00] mt-1">{{ number_format($stats['pending_cash_count'] ?? 0, 0, ',', '.') }}</h3>
            <p class="text-[11px] text-amber-700 mt-2 font-medium">Menunggu dibayar ke Kasir</p>
        </div>
    </div>

    <!-- Charts Section (Twitter Blue Palette Pie/Donut & Line Chart) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Sales Hourly / Daily Trend -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-5 sm:p-6 border border-[#eff3f4] shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="font-black text-base text-[#0f1419]" x-text="timeframeTitle">Tren Transaksi Penjualan</h3>
                    <p class="text-xs text-[#536471]" x-text="timeframeSubtitle">Aktivitas omzet penjualan kasir</p>
                </div>
                <!-- Timeframe Segmented Control Buttons (Twitter UI Pill Style) -->
                <div class="inline-flex p-1 bg-[#f7f9f9] border border-[#eff3f4] rounded-full self-start sm:self-auto shadow-2xs">
                    <button 
                        @click="setTimeframe('1d')" 
                        type="button" 
                        class="px-3.5 py-1 rounded-full text-xs font-black transition-all cursor-pointer"
                        :class="chartTimeframe === '1d' ? 'bg-[#1d9bf0] text-white shadow-xs' : 'text-[#536471] hover:text-[#0f1419]'"
                    >
                        1 Hari
                    </button>
                    <button 
                        @click="setTimeframe('7d')" 
                        type="button" 
                        class="px-3.5 py-1 rounded-full text-xs font-black transition-all cursor-pointer"
                        :class="chartTimeframe === '7d' ? 'bg-[#1d9bf0] text-white shadow-xs' : 'text-[#536471] hover:text-[#0f1419]'"
                    >
                        7 Hari
                    </button>
                    <button 
                        @click="setTimeframe('30d')" 
                        type="button" 
                        class="px-3.5 py-1 rounded-full text-xs font-black transition-all cursor-pointer"
                        :class="chartTimeframe === '30d' ? 'bg-[#1d9bf0] text-white shadow-xs' : 'text-[#536471] hover:text-[#0f1419]'"
                    >
                        1 Bulan
                    </button>
                </div>
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
                            <td class="px-4 py-3 font-black text-[#0f1419]">
                                <span class="px-2 py-0.5 rounded-lg bg-[#e8f5fd] text-[#1d9bf0] text-[10px] font-black mr-1" x-text="`#${String(tx.id || 0).padStart(4, '0')}`"></span>
                                <span x-text="tx.invoice_code"></span>
                            </td>
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
                                        'bg-amber-50 text-[#ff7a00] border border-amber-200': tx.status === 'pending_verification' || tx.status === 'pending',
                                        'bg-rose-50 text-[#f4212e] border border-rose-200': tx.status === 'rejected',
                                        'bg-slate-100 text-slate-500 line-through': tx.status === 'cancelled'
                                    }"
                                    x-text="tx.status === 'pending_verification' ? 'Pending Verif' : (tx.status === 'pending' ? 'Belum Bayar' : tx.status)"
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
