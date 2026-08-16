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

        return view('admin.warung', compact('activeEvent', 'stores'));
    }

    public function show(Store $store): JsonResponse
    {
        return response()->json([
            'success' => true,
            'store' => $store->load(['owner', 'products', 'transactions.revenueSplit']),
        ]);
    }
}
