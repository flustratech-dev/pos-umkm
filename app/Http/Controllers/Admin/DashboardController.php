<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Store;
use App\Models\Transaction;
use App\Services\ReportService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(): View
    {
        $activeEvent = Event::getActive();
        $stats = $this->reportService->getEventStats($activeEvent);

        $recentTransactions = $activeEvent
            ? Transaction::whereHas('store', function ($q) use ($activeEvent) {
                $q->where('event_id', $activeEvent->id);
            })
            ->with(['store', 'revenueSplit'])
            ->latest()
            ->take(10)
            ->get()
            : collect();

        $stores = $activeEvent ? Store::where('event_id', $activeEvent->id)->get() : collect();

        return view('admin.dashboard', compact('activeEvent', 'stats', 'recentTransactions', 'stores'));
    }
}
