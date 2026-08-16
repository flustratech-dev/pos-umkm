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

class QrisVerificationController extends Controller
{
    public function __construct(
        protected TransactionVerificationService $verificationService
    ) {}

    public function index(Request $request): View
    {
        $activeEvent = Event::getActive();

        $query = Transaction::where('payment_method', 'qris')
            ->with(['store', 'paymentProof', 'cashier', 'items'])
            ->latest();

        if ($activeEvent) {
            $query->whereHas('store', function ($q) use ($activeEvent) {
                $q->where('event_id', $activeEvent->id);
            });
        }

        $pendingTransactions = (clone $query)->where('status', 'pending_verification')->get();
        $historyTransactions = (clone $query)->whereIn('status', ['paid', 'rejected'])->take(20)->get();

        return view('admin.verifikasi-qris', compact('activeEvent', 'pendingTransactions', 'historyTransactions'));
    }

    public function approve(Transaction $transaction): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        try {
            $this->verificationService->approve($transaction, $user);

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Transaksi {$transaction->invoice_code} berhasil diverifikasi (Paid)!",
                    'transaction' => $transaction,
                ]);
            }

            return redirect()->route('admin.verifikasi-qris.index')->with('success', "Transaksi {$transaction->invoice_code} berhasil diverifikasi!");
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

    public function reject(Request $request, Transaction $transaction): JsonResponse|RedirectResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        try {
            $this->verificationService->reject($transaction, $user, $request->input('rejection_reason'));

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Transaksi {$transaction->invoice_code} telah ditolak.",
                    'transaction' => $transaction,
                ]);
            }

            return redirect()->route('admin.verifikasi-qris.index')->with('info', "Transaksi {$transaction->invoice_code} ditolak.");
        } catch (Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
