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
        $historyTransactions = (clone $query)->whereIn('status', ['paid', 'rejected', 'cancelled'])->take(20)->get();

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

    /**
     * Reject pending cash transaction.
     */
    public function reject(Request $request, Transaction $transaction): JsonResponse|RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        try {
            $this->verificationService->reject($transaction, $user, $request->input('reason'));

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Transaksi cash {$transaction->invoice_code} berhasil ditolak.",
                    'transaction' => $transaction,
                ]);
            }

            return redirect()->route('admin.verifikasi-cash.index')
                ->with('info', "Transaksi cash {$transaction->invoice_code} berhasil ditolak.");
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

    /**
     * Complete pending cash transaction without payment (clears from queue and marks as Tanpa Pembayaran).
     */
    public function completeWithoutPayment(Transaction $transaction): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        try {
            $this->verificationService->completeWithoutPayment($transaction, $user, 'Tanpa Pembayaran');

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Transaksi {$transaction->invoice_code} berhasil diselesaikan tanpa pembayaran.",
                    'transaction' => $transaction,
                ]);
            }

            return redirect()->route('admin.verifikasi-cash.index')
                ->with('success', "Transaksi {$transaction->invoice_code} berhasil diselesaikan tanpa pembayaran.");
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

    /**
     * Delete pending or rejected cash transaction permanently (useful for test/anomaly transactions).
     */
    public function destroy(Transaction $transaction): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        if ($transaction->status === 'paid') {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi yang sudah lunas (Paid) tidak dapat dihapus langsung. Gunakan fitur batalkan transaksi.',
                ], 422);
            }
            return redirect()->back()->with('error', 'Transaksi yang sudah lunas tidak dapat dihapus langsung.');
        }

        try {
            $invoiceCode = $transaction->invoice_code;

            \Illuminate\Support\Facades\DB::transaction(function () use ($transaction) {
                $transaction->items()->delete();
                if ($transaction->paymentProof) {
                    $transaction->paymentProof()->delete();
                }
                if ($transaction->revenueSplit) {
                    $transaction->revenueSplit()->delete();
                }
                $transaction->delete();
            });

            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Transaksi {$invoiceCode} berhasil dihapus permanen.",
                ]);
            }

            return redirect()->route('admin.verifikasi-cash.index')
                ->with('success', "Transaksi {$invoiceCode} berhasil dihapus permanen.");
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
