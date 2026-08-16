<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Transaction;
use App\Services\ReportService;
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

        $paidTransactions = $query->get();

        return view('superadmin.laporan', compact('platformStats', 'events', 'selectedEventId', 'paidTransactions'));
    }

    public function downloadPdf(Request $request): Response
    {
        $platformStats = $this->reportService->getPlatformStats();
        $paidTransactions = Transaction::where('status', 'paid')
            ->with(['store.event', 'revenueSplit'])
            ->latest('paid_at')
            ->get();

        $events = Event::with('stores')->get();

        $pdf = Pdf::loadView('reports.superadmin-pdf', compact('platformStats', 'paidTransactions', 'events'))
            ->setPaper('a4', 'portrait');

        $fileName = 'Laporan_SuperAdmin_Fee_' . date('Ymd_His') . '.pdf';

        return $pdf->download($fileName);
    }
}
