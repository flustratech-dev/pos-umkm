<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Store;
use App\Models\Transaction;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(Request $request): View
    {
        $user = Auth::user();
        $store = $user->store ?: Store::where('owner_id', $user->id)->first();
        $activeEvent = Event::getActive();

        $stats = $store ? $this->reportService->getStoreStats($store) : [
            'total_gross' => 0,
            'owner_share' => 0,
            'paid_count' => 0,
            'cash_count' => 0,
            'qris_count' => 0,
        ];

        $statusFilter = $request->query('status', 'all');

        $query = Transaction::where('store_id', $store?->id)
            ->with(['items', 'revenueSplit'])
            ->latest();

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $transactions = $store ? $query->get() : collect();

        return view('user.laporan', compact('user', 'store', 'activeEvent', 'stats', 'transactions', 'statusFilter'));
    }

    public function downloadPdf(): Response
    {
        $user = Auth::user();
        $store = $user->store ?: Store::where('owner_id', $user->id)->firstOrFail();
        $activeEvent = Event::getActive();

        $stats = $this->reportService->getStoreStats($store);
        $transactions = Transaction::where('store_id', $store->id)
            ->with(['items', 'revenueSplit'])
            ->latest()
            ->get();

        $pdf = Pdf::loadView('reports.user-pdf', compact('user', 'store', 'activeEvent', 'stats', 'transactions'))
            ->setPaper('a4', 'portrait');

        $fileName = 'Laporan_Penjualan_' . str_replace(' ', '_', $store->name) . '_' . date('Ymd_His') . '.pdf';

        return $pdf->download($fileName);
    }
}
