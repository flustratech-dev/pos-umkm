<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\CashCheckoutRequest;
use App\Http\Requests\QrisCheckoutRequest;
use App\Models\Event;
use App\Models\Product;
use App\Models\Store;
use App\Models\Transaction;
use App\Services\CheckoutService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KasirController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService
    ) {}

    public function index(): View
    {
        $user = Auth::user();
        $store = $user->store ?: Store::where('owner_id', $user->id)->first();
        $activeEvent = Event::getActive();

        $products = $store 
            ? Product::where('store_id', $store->id)->where('is_active', true)->get()
            : collect();

        $recentTransactions = $store
            ? Transaction::where('store_id', $store->id)
                ->with(['items', 'revenueSplit'])
                ->latest()
                ->take(5)
                ->get()
            : collect();

        return view('user.kasir', compact('user', 'store', 'activeEvent', 'products', 'recentTransactions'));
    }

    public function checkoutCash(CashCheckoutRequest $request): JsonResponse
    {
        $user = Auth::user();
        $store = $user->store ?: Store::where('owner_id', $user->id)->firstOrFail();

        if (!$store->event->is_active) {
            return response()->json(['success' => false, 'message' => 'Kasir ditutup karena event sudah inaktif.'], 403);
        }

        try {
            $transaction = $this->checkoutService->processCashCheckout(
                $store,
                $user,
                $request->input('items', []),
                (float) $request->input('amount_paid')
            );

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran tunai berhasil diproses!',
                'transaction' => $transaction,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function checkoutQris(QrisCheckoutRequest $request): JsonResponse
    {
        $user = Auth::user();
        $store = $user->store ?: Store::where('owner_id', $user->id)->firstOrFail();
        
        if (!$store->event->is_active) {
            return response()->json(['success' => false, 'message' => 'Kasir ditutup karena event sudah inaktif.'], 403);
        }

        try {
            $transaction = $this->checkoutService->processQrisCheckout(
                $store,
                $user,
                $request->input('items', []),
                $request->file('proof_image')
            );

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran QRIS berhasil! Struk siap dicetak.',
                'transaction' => $transaction,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function switchStore(Request $request)
    {
        $request->validate(['store_id' => 'required|exists:stores,id']);
        $user = Auth::user();
        
        $targetStore = Store::where('id', $request->store_id)
            ->where('owner_id', $user->id)
            ->firstOrFail();
            
        $user->update(['store_id' => $targetStore->id]);
        
        return redirect()->back()->with('success', 'Berhasil beralih ke warung: ' . $targetStore->event->name);
    }

    public function generateQris(Request $request): JsonResponse
    {
        $request->validate(['amount' => 'required|numeric|min:1']);
        $user = Auth::user();
        $store = $user->store ?: Store::where('owner_id', $user->id)->firstOrFail();
        $event = $store->event;

        if (!$store->use_dynamic_qris || !$event->qris_payload) {
            return response()->json(['success' => false, 'message' => 'QRIS Dinamis tidak aktif atau payload tidak tersedia.']);
        }

        $amount = (float) $request->amount;
        // Kode unik dari kode tenda, sama dengan yang dipakai saat checkout.
        $amount += $store->unique_code;

        $qrisService = new \App\Services\Payment\QrisService();
        $dynamicPayload = $qrisService->convertToDynamic($event->qris_payload, $amount);

        return response()->json([
            'success' => true,
            'qris_payload' => $dynamicPayload,
        ]);
    }
}
