<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Store;
use App\Models\Transaction;

class ReportService
{
    /**
     * Get statistics for a specific store.
     */
    public function getStoreStats(Store $store): array
    {
        $paidTransactions = Transaction::where('store_id', $store->id)
            ->where('status', 'paid')
            ->with('revenueSplit')
            ->get();

        $totalGross = (float) $paidTransactions->sum('total_amount');
        $ownerShare = (float) $paidTransactions->sum(function ($tx) {
            return $tx->revenueSplit ? (float) $tx->revenueSplit->owner_share : round($tx->total_amount * RevenueSplitService::OWNER_PERCENTAGE, 2);
        });

        $cashCount = $paidTransactions->where('payment_method', 'cash')->count();
        $qrisCount = $paidTransactions->where('payment_method', 'qris')->count();

        // Pending cash transactions count
        $pendingCashCount = Transaction::where('store_id', $store->id)
            ->where('status', 'pending')
            ->where('payment_method', 'cash')
            ->count();

        // Pending QRIS verification count
        $pendingQrisCount = Transaction::where('store_id', $store->id)
            ->where('status', 'pending_verification')
            ->where('payment_method', 'qris')
            ->count();

        return [
            'total_gross' => $totalGross,
            'owner_share' => $ownerShare,
            'paid_count' => $paidTransactions->count(),
            'cash_count' => $cashCount,
            'qris_count' => $qrisCount,
            'pending_cash_count' => $pendingCashCount,
            'pending_qris_count' => $pendingQrisCount,
        ];
    }

    /**
     * Get statistics for an Event (Admin EO view).
     */
    /**
     * Data grafik tren penjualan untuk dashboard EO.
     *
     * Dihitung di server dari SELURUH transaksi lunas milik event, bukan dari
     * potongan transaksi terakhir yang dikirim ke browser — itu yang dulu
     * membuat grafiknya rata nol padahal transaksi ramai.
     *
     * @return array{1d: array, 7d: array, 30d: array}
     */
    public function getSalesTrend(?Event $event = null): array
    {
        $event = $event ?? Event::getActive();

        if (!$event) {
            return $this->emptyTrend();
        }

        $storeIds = Store::where('event_id', $event->id)->pluck('id');

        if ($storeIds->isEmpty()) {
            return $this->emptyTrend();
        }

        $sejak = now()->copy()->subDays(29)->startOfDay();

        $transactions = Transaction::whereIn('store_id', $storeIds)
            ->where('status', 'paid')
            ->where(function ($q) use ($sejak) {
                $q->where('paid_at', '>=', $sejak)
                    ->orWhere(function ($q2) use ($sejak) {
                        $q2->whereNull('paid_at')->where('created_at', '>=', $sejak);
                    });
            })
            ->get(['payment_method', 'total_amount', 'paid_at', 'created_at']);

        return [
            '1d' => $this->trendPerJam($transactions),
            '7d' => $this->trendPerHari($transactions, 7),
            '30d' => $this->trendPerHari($transactions, 30),
        ];
    }

    protected function emptyTrend(): array
    {
        return [
            '1d' => $this->trendPerJam(collect()),
            '7d' => $this->trendPerHari(collect(), 7),
            '30d' => $this->trendPerHari(collect(), 30),
        ];
    }

    /**
     * Sepanjang hari ini, jam 00:00 sampai 23:00 — bukan cuma jam kerja,
     * supaya transaksi pagi buta dan larut malam tetap kelihatan.
     */
    protected function trendPerJam($transactions): array
    {
        $labels = [];
        $cash = array_fill(0, 24, 0.0);
        $qris = array_fill(0, 24, 0.0);

        for ($jam = 0; $jam < 24; $jam++) {
            $labels[] = str_pad((string) $jam, 2, '0', STR_PAD_LEFT) . ':00';
        }

        $hariIni = now()->toDateString();

        foreach ($transactions as $tx) {
            $waktu = $tx->paid_at ?: $tx->created_at;
            if (!$waktu || $waktu->toDateString() !== $hariIni) {
                continue;
            }

            $jam = (int) $waktu->format('G');
            $nilai = (float) $tx->total_amount;

            if ($tx->payment_method === 'cash') {
                $cash[$jam] += $nilai;
            } else {
                $qris[$jam] += $nilai;
            }
        }

        return ['labels' => $labels, 'cash' => $cash, 'qris' => $qris];
    }

    protected function trendPerHari($transactions, int $jumlahHari): array
    {
        $labels = [];
        $kunci = [];
        $cash = [];
        $qris = [];

        for ($i = $jumlahHari - 1; $i >= 0; $i--) {
            $tanggal = now()->copy()->subDays($i);
            $labels[] = $tanggal->translatedFormat('d M');
            $kunci[] = $tanggal->toDateString();
            $cash[] = 0.0;
            $qris[] = 0.0;
        }

        $indeks = array_flip($kunci);

        foreach ($transactions as $tx) {
            $waktu = $tx->paid_at ?: $tx->created_at;
            if (!$waktu) {
                continue;
            }

            $tanggal = $waktu->toDateString();
            if (!isset($indeks[$tanggal])) {
                continue;
            }

            $posisi = $indeks[$tanggal];
            $nilai = (float) $tx->total_amount;

            if ($tx->payment_method === 'cash') {
                $cash[$posisi] += $nilai;
            } else {
                $qris[$posisi] += $nilai;
            }
        }

        return ['labels' => $labels, 'cash' => $cash, 'qris' => $qris];
    }

    public function getEventStats(?Event $event = null): array
    {
        $event = $event ?? Event::getActive();
        if (!$event) {
            return [
                'total_gross' => 0.0,
                'admin_gross' => 0.0,
                'superadmin_total' => 0.0,
                'admin_net' => 0.0,
                'owner_total' => 0.0,
                'paid_count' => 0,
                'pending_count' => 0,
                'pending_cash_count' => 0,
                'pending_qris_count' => 0,
                'stores_count' => 0,
                'cash_count' => 0,
                'qris_count' => 0,
            ];
        }

        $storeIds = Store::where('event_id', $event->id)->pluck('id');

        $paidTransactions = Transaction::whereIn('store_id', $storeIds)
            ->where('status', 'paid')
            ->with('revenueSplit')
            ->get();

        $totalGross = (float) $paidTransactions->sum('total_amount');
        $ownerTotal = (float) $paidTransactions->sum(function ($tx) {
            return $tx->revenueSplit ? (float) $tx->revenueSplit->owner_share : round($tx->total_amount * RevenueSplitService::OWNER_PERCENTAGE, 2);
        });
        $adminGross = (float) $paidTransactions->sum(function ($tx) {
            return $tx->revenueSplit ? (float) $tx->revenueSplit->admin_gross_share : round($tx->total_amount * RevenueSplitService::ADMIN_PERCENTAGE, 2);
        });
        $superadminTotal = (float) $paidTransactions->sum(function ($tx) {
            return $tx->revenueSplit ? (float) $tx->revenueSplit->superadmin_share : round($tx->total_amount * RevenueSplitService::SUPERADMIN_PERCENTAGE, 2);
        });
        $adminNet = (float) $paidTransactions->sum(function ($tx) {
            return $tx->revenueSplit ? (float) $tx->revenueSplit->admin_net_share : round($tx->total_amount * RevenueSplitService::ADMIN_PERCENTAGE, 2);
        });

        $pendingCashCount = Transaction::whereIn('store_id', $storeIds)
            ->where('status', 'pending')
            ->where('payment_method', 'cash')
            ->count();

        $pendingQrisCount = Transaction::whereIn('store_id', $storeIds)
            ->where('status', 'pending_verification')
            ->count();

        $pendingCount = $pendingCashCount + $pendingQrisCount;

        $cashCount = $paidTransactions->where('payment_method', 'cash')->count();
        $qrisCount = $paidTransactions->where('payment_method', 'qris')->count();

        return [
            'total_gross' => $totalGross,
            'admin_gross' => $adminGross,
            'superadmin_total' => $superadminTotal,
            'admin_net' => $adminNet,
            'owner_total' => $ownerTotal,
            'paid_count' => $paidTransactions->count(),
            'pending_count' => $pendingCount,
            'pending_cash_count' => $pendingCashCount,
            'pending_qris_count' => $pendingQrisCount,
            'stores_count' => $storeIds->count(),
            'cash_count' => $cashCount,
            'qris_count' => $qrisCount,
        ];
    }

    /**
     * Get platform statistics across all events (Super Admin view).
     */
    public function getPlatformStats(): array
    {
        $paidTransactions = Transaction::where('status', 'paid')
            ->with('revenueSplit')
            ->get();

        $totalPlatformGross = (float) $paidTransactions->sum('total_amount');
        $totalSuperadminFee = (float) $paidTransactions->sum(function ($tx) {
            return $tx->revenueSplit ? (float) $tx->revenueSplit->superadmin_share : round($tx->total_amount * RevenueSplitService::SUPERADMIN_PERCENTAGE, 2);
        });

        $totalEvents = Event::count();
        $totalStores = Store::count();

        return [
            'total_platform_gross' => $totalPlatformGross,
            'total_superadmin_fee' => $totalSuperadminFee,
            'paid_count' => $paidTransactions->count(),
            'total_events' => $totalEvents,
            'total_stores' => $totalStores,
        ];
    }
}
