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
            return $tx->revenueSplit ? (float) $tx->revenueSplit->owner_share : round($tx->total_amount * 0.75, 2);
        });

        $cashCount = $paidTransactions->where('payment_method', 'cash')->count();
        $qrisCount = $paidTransactions->where('payment_method', 'qris')->count();

        return [
            'total_gross' => $totalGross,
            'owner_share' => $ownerShare,
            'paid_count' => $paidTransactions->count(),
            'cash_count' => $cashCount,
            'qris_count' => $qrisCount,
        ];
    }

    /**
     * Get statistics for an Event (Admin EO view).
     */
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
            return $tx->revenueSplit ? (float) $tx->revenueSplit->owner_share : round($tx->total_amount * 0.75, 2);
        });
        $adminGross = (float) $paidTransactions->sum(function ($tx) {
            return $tx->revenueSplit ? (float) $tx->revenueSplit->admin_gross_share : round($tx->total_amount * 0.25, 2);
        });
        $superadminTotal = (float) $paidTransactions->sum(function ($tx) {
            return $tx->revenueSplit ? (float) $tx->revenueSplit->superadmin_share : 1000.0;
        });
        $adminNet = (float) $paidTransactions->sum(function ($tx) {
            return $tx->revenueSplit ? (float) $tx->revenueSplit->admin_net_share : round(($tx->total_amount * 0.25) - 1000, 2);
        });

        $pendingCount = Transaction::whereIn('store_id', $storeIds)
            ->where('status', 'pending_verification')
            ->count();

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
            return $tx->revenueSplit ? (float) $tx->revenueSplit->superadmin_share : 1000.0;
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
