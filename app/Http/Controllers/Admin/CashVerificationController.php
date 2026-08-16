<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Transaction;
use App\Services\TransactionVerificationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CashVerificationController extends Controller
{
    public function __construct(
        protected TransactionVerificationService $verificationService
    ) {}

    /**
     * Show cash transactions pending admin confirmation.
     */
    public function index(Request $request): View
    {
        $activeEvent = Event::getActive();

        $query = Transaction::where('payment_method', 'cash')
            ->with(['store', 'cashier', 'items'])
            ->latest();

        if ($activeEvent) {
            $query->whereHas('store', function ($q) use ($activeEvent) {
                $q->where('event_id', $activeEvent->id);
            });
        }

        $pendingTransactions = (clone $query)->where('status', 'pending')->get();
        $historyTransactions = (clone $query)->whereIn('status', ['paid', 'cancelled'])->take(20)->get();

        return view('admin.verifikasi-cash', compact('activeEvent', 'pendingTransactions', 'historyTransactions'));
    }

    /**
     * Confirm cash payment received at exit cashier booth.
     */
    public function confirm(Transaction $transaction): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        try {
            $this->verificationService->confirmCash($transaction, $user);

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Pembayaran cash {$transaction->invoice_code} berhasil dikonfirmasi!",
                    'transaction' => $transaction->load(['store', 'revenueSplit']),
                ]);
            }

            return redirect()->route('admin.verifikasi-cash.index')
                ->with('success', "Pembayaran cash {$transaction->invoice_code} berhasil dikonfirmasi!");
        } catch (Exception $e) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
