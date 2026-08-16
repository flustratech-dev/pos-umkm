<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $activeEvent = Event::getActive();

        $query = Product::with('store')
            ->latest();

        if ($activeEvent) {
            $query->whereHas('store', function ($q) use ($activeEvent) {
                $q->where('event_id', $activeEvent->id);
            });
        }

        $products = $query->get();
        $stores = $activeEvent ? Store::where('event_id', $activeEvent->id)->get() : collect();

        return view('admin.produk', compact('activeEvent', 'products', 'stores'));
    }
}
