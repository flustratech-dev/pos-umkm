<?php

namespace App\Http\Controllers\SuperAdmin;

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
        $platformStats = $this->reportService->getPlatformStats();
        $activeEvent = Event::getActive();
        $events = Event::with('stores')->latest()->get();

        return view('superadmin.dashboard', compact('platformStats', 'activeEvent', 'events'));
    }
}
