<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function index(Request $request): View
    {
        $activeEvent = Event::getActive();

        $query = Store::with(['owner', 'products', 'transactions'])
            ->latest();

        if ($activeEvent) {
            $query->where('event_id', $activeEvent->id);
        }

        $stores = $query->get();

        $inactiveStores = collect();
        if ($activeEvent) {
            $inactiveStores = Store::with(['owner', 'event'])
                ->where('event_id', '!=', $activeEvent->id)
                ->whereNotIn('owner_id', $stores->pluck('owner_id')->toArray())
                ->latest()
                ->get();
        }

        return view('admin.warung', compact('activeEvent', 'stores', 'inactiveStores'));
    }

    public function show(Store $store): JsonResponse
    {
        return response()->json([
            'success' => true,
            'store' => $store->load(['owner', 'products', 'transactions.revenueSplit']),
        ]);
    }
    public function pull(Request $request)
    {
        $request->validate([
            'old_store_id' => 'required|exists:stores,id',
        ]);

        $activeEvent = Event::getActive();
        if (!$activeEvent) {
            return redirect()->back()->with('error', 'Tidak ada event yang sedang aktif!');
        }

        $oldStore = Store::findOrFail($request->old_store_id);

        if ($oldStore->event_id === $activeEvent->id) {
            return redirect()->back()->with('error', 'Warung ini sudah berada di event yang aktif saat ini.');
        }

        // Cek apakah owner sudah punya toko di event aktif
        $exists = Store::where('owner_id', $oldStore->owner_id)
            ->where('event_id', $activeEvent->id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Pemilik warung ini sudah terdaftar di event yang aktif!');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($oldStore, $activeEvent) {
            // Duplicate store
            $newStore = $oldStore->replicate();
            $newStore->event_id = $activeEvent->id;
            $newStore->created_at = now();
            $newStore->updated_at = now();
            $newStore->save();

            // Duplicate products
            $oldProducts = \App\Models\Product::where('store_id', $oldStore->id)->get();
            foreach ($oldProducts as $product) {
                $newProduct = $product->replicate();
                $newProduct->store_id = $newStore->id;
                $newProduct->save();
            }

            // Update user active store
            $owner = \App\Models\User::find($oldStore->owner_id);
            if ($owner) {
                $owner->update(['store_id' => $newStore->id]);
            }
        });

        return redirect()->back()->with('success', 'Warung berhasil ditarik ke event aktif beserta semua produknya!');
    }
}
