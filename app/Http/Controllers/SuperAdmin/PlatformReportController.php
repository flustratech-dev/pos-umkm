<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Transaction;
use App\Services\ReportExportService;
use App\Services\ReportService;
use App\Support\ReportPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PlatformReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(Request $request): View
    {
        $platformStats = $this->reportService->getPlatformStats();
        $events = Event::with('stores')->get();

        $selectedEventId = $request->query('event_id', 'all');

        $query = Transaction::where('status', 'paid')
            ->with(['store.event', 'revenueSplit'])
            ->latest('paid_at');

        if ($selectedEventId !== 'all') {
            $query->whereHas('store', function ($q) use ($selectedEventId) {
                $q->where('event_id', $selectedEventId);
            });
        }

        $period = ReportPeriod::fromRequest($request);
        $period->apply($query, 'paid_at');

        $paidTransactions = $query->get();

        return view('superadmin.laporan', compact('platformStats', 'events', 'selectedEventId', 'paidTransactions', 'period'));
    }

    public function downloadPdf(Request $request, ReportExportService $exporter)
    {
        $period = ReportPeriod::fromRequest($request);

        $format = $request->query('format', 'pdf');
        if (!in_array($format, ReportExportService::FORMATS, true)) {
            $format = 'pdf';
        }

        $platformStats = $this->reportService->getPlatformStats();

        $query = Transaction::where('status', 'paid')
            ->with(['store.event', 'revenueSplit', 'items', 'cashier', 'paymentProof'])
            ->latest('paid_at');

        // Laporan platform dihitung dari waktu lunas, bukan waktu transaksi dibuat.
        $period->apply($query, 'paid_at');
        $paidTransactions = $query->get();

        $events = Event::with('stores')->get();
        $judul = 'Laporan Platform Fee';

        if ($format === 'csv') {
            return $exporter->csv($paidTransactions, $period, $judul);
        }

        if ($format === 'xlsx') {
            return $exporter->xlsx($paidTransactions, $period, $judul);
        }

        $pdf = Pdf::loadView('reports.superadmin-pdf', compact('platformStats', 'paidTransactions', 'events', 'period'))
            ->setPaper('a4', 'portrait');

        return $pdf->download($exporter->fileName($judul, $period, 'pdf'));
    }
}
