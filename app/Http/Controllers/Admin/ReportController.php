<?php

namespace App\Http\Controllers\Admin;

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
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(Request $request): View
    {
        $activeEvent = Event::getActive();
        $stats = $this->reportService->getEventStats($activeEvent);

        $statusFilter = $request->query('status', 'all');
        $storeFilter = $request->query('store_id', 'all');

        $query = Transaction::with(['store', 'revenueSplit', 'canceller', 'verifier'])
            ->latest();

        if ($activeEvent) {
            $query->whereHas('store', function ($q) use ($activeEvent) {
                $q->where('event_id', $activeEvent->id);
            });
        }

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if ($storeFilter !== 'all') {
            $query->where('store_id', $storeFilter);
        }

        $period = ReportPeriod::fromRequest($request);
        $period->apply($query);

        $transactions = $query->get();
        $stores = $activeEvent ? Store::where('event_id', $activeEvent->id)->get() : collect();

        return view('admin.laporan', compact('activeEvent', 'stats', 'transactions', 'stores', 'statusFilter', 'storeFilter', 'period'));
    }

    public function downloadPdf(Request $request, ReportExportService $exporter)
    {
        $activeEvent = Event::getActive();
        $period = ReportPeriod::fromRequest($request);

        $format = $request->query('format', 'pdf');
        if (!in_array($format, ReportExportService::FORMATS, true)) {
            $format = 'pdf';
        }

        // 1. Laporan khusus untuk satu tenant / warung tertentu
        if ($request->filled('store_id') && $request->store_id !== 'all') {
            $store = Store::with('owner')->findOrFail($request->store_id);
            $user = $store->owner ?: auth()->user();
            $stats = $this->reportService->getStoreStats($store);

            $query = Transaction::where('store_id', $store->id)
                ->with(['items', 'revenueSplit', 'cashier', 'paymentProof'])
                ->latest();
            $period->apply($query);
            $transactions = $query->get();

            $judul = 'Laporan Stand ' . $store->name;

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

        // 2. Default: laporan keseluruhan event
        $stats = $this->reportService->getEventStats($activeEvent);

        $query = Transaction::with(['store', 'revenueSplit', 'items', 'cashier', 'paymentProof'])
            ->latest();

        if ($activeEvent) {
            $query->whereHas('store', function ($q) use ($activeEvent) {
                $q->where('event_id', $activeEvent->id);
            });
        }

        $period->apply($query);
        $transactions = $query->get();
        $stores = $activeEvent ? Store::where('event_id', $activeEvent->id)->get() : collect();

        $judul = 'Laporan EO ' . ($activeEvent ? $activeEvent->name : 'Event');

        if ($format === 'csv') {
            return $exporter->csv($transactions, $period, $judul);
        }

        if ($format === 'xlsx') {
            return $exporter->xlsx($transactions, $period, $judul);
        }

        $pdf = Pdf::loadView('reports.admin-pdf', compact('activeEvent', 'stats', 'transactions', 'stores', 'period'))
            ->setPaper('a4', 'landscape');

        return $pdf->download($exporter->fileName($judul, $period, 'pdf'));
    }
}
