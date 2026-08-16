<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function show(Transaction $transaction): JsonResponse
    {
        return response()->json([
            'success' => true,
            'transaction' => $transaction->load(['items', 'store.event', 'cashier']),
        ]);
    }

    public function print(Transaction $transaction): View
    {
        $transaction->load(['items', 'store.event', 'cashier']);
        return view('receipts.thermal', compact('transaction'));
    }
}
