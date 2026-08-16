<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelTransactionRequest;
use App\Models\Transaction;
use App\Services\TransactionCancellationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionCancellationService $cancellationService
    ) {}

    public function cancel(CancelTransactionRequest $request, Transaction $transaction): JsonResponse|RedirectResponse
    {
        $user = Auth::user();

        try {
            $this->cancellationService->cancel(
                $transaction,
                $user,
                $request->input('reason_category'),
                $request->input('custom_note'),
                $request->boolean('refund_ack_confirmed')
            );

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Transaksi {$transaction->invoice_code} berhasil dibatalkan.",
                    'transaction' => $transaction,
                ]);
            }

            return redirect()->back()->with('success', "Transaksi {$transaction->invoice_code} berhasil dibatalkan.");
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
