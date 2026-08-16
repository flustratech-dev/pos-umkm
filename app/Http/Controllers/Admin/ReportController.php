<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Store;
use App\Models\Transaction;
use App\Services\ReportService;
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

        $transactions = $query->get();
        $stores = $activeEvent ? Store::where('event_id', $activeEvent->id)->get() : collect();

        return view('admin.laporan', compact('activeEvent', 'stats', 'transactions', 'stores', 'statusFilter', 'storeFilter'));
    }

    public function downloadPdf(Request $request): Response
    {
        $activeEvent = Event::getActive();
        $stats = $this->reportService->getEventStats($activeEvent);

        $query = Transaction::with(['store', 'revenueSplit'])
            ->latest();

        if ($activeEvent) {
            $query->whereHas('store', function ($q) use ($activeEvent) {
                $q->where('event_id', $activeEvent->id);
            });
        }

        $transactions = $query->get();
        $stores = $activeEvent ? Store::where('event_id', $activeEvent->id)->get() : collect();

        $pdf = Pdf::loadView('reports.admin-pdf', compact('activeEvent', 'stats', 'transactions', 'stores'))
            ->setPaper('a4', 'landscape');

        $fileName = 'Laporan_EO_' . ($activeEvent ? str_replace(' ', '_', $activeEvent->name) : 'Event') . '_' . date('Ymd_His') . '.pdf';

        return $pdf->download($fileName);
    }
}
