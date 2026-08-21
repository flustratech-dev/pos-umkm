<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Store;
use App\Models\Transaction;
use App\Services\ReportExportService;
use App\Services\ReportService;
use App\Support\ReportPeriod;
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

        $period = ReportPeriod::fromRequest($request);
        $period->apply($query);

        $transactions = $store ? $query->get() : collect();

        return view('user.laporan', compact('user', 'store', 'activeEvent', 'stats', 'transactions', 'statusFilter', 'period'));
    }

    public function downloadPdf(Request $request, ReportExportService $exporter)
    {
        $user = Auth::user();
        $store = $user->store ?: Store::where('owner_id', $user->id)->firstOrFail();
        $activeEvent = Event::getActive();

        $period = ReportPeriod::fromRequest($request);
        $format = $request->query('format', 'pdf');
        if (!in_array($format, ReportExportService::FORMATS, true)) {
            $format = 'pdf';
        }

        $stats = $this->reportService->getStoreStats($store);

        $query = Transaction::where('store_id', $store->id)
            ->with(['items', 'revenueSplit', 'cashier', 'paymentProof'])
            ->latest();
        $period->apply($query);
        $transactions = $query->get();

        $judul = 'Laporan Penjualan ' . $store->name;

        if ($format === 'csv') {
            return $exporter->csv($transactions, $period, $judul, withStore: false);
        }

        if ($format === 'xlsx') {
            return $exporter->xlsx($transactions, $period, $judul, withStore: false);
        }

        $pdf = Pdf::loadView('reports.user-pdf', compact('user', 'store', 'activeEvent', 'stats', 'transactions', 'period'))
            ->setPaper('a4', 'portrait');

        return $pdf->download($exporter->fileName($judul, $period, 'pdf'));
    }
}
