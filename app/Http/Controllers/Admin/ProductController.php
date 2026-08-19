<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Event;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    public function store(ProductRequest $request): JsonResponse|RedirectResponse
    {
        $store = Store::findOrFail($request->store_id);

        if (!$store->event->is_active) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat menambah produk karena event sudah inaktif.'], 403);
        }

        $photoPath = $request->input('photo');
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('products', 'public');
        }

        $product = Product::create(array_merge($request->priceAttributes(), [
            'store_id' => $store->id,
            'title' => $request->title,
            'category' => $request->category ?: Product::DEFAULT_CATEGORY,
            'description' => $request->description,
            'photo' => $photoPath,
            'stock_badge' => $request->stock_badge ?: 'Tersedia',
            'is_active' => $request->boolean('is_active', true),
        ]));

        // Kartu produk menampilkan nama warung, jadi relasinya harus ikut terkirim.
        $product->load('store');

        $productData = array_merge($product->toArray(), [
            'photo' => $product->photo_url,
            'photo_url' => $product->photo_url,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Produk menu berhasil ditambahkan!',
                'product' => $productData,
            ]);
        }

        return redirect()->route('admin.produk')->with('success', 'Produk menu berhasil ditambahkan!');
    }

    public function update(ProductRequest $request, Product $product): JsonResponse|RedirectResponse
    {
        if (!$product->store->event->is_active) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat mengubah produk karena event sudah inaktif.'], 403);
        }

        $data = array_merge($request->priceAttributes(), [
            'title' => $request->title,
            'category' => $request->category ?: $product->category,
            'description' => $request->description,
            'stock_badge' => $request->stock_badge ?: $product->stock_badge,
            'is_active' => $request->boolean('is_active', true),
            'store_id' => $request->store_id ?: $product->store_id, // Allow admin to change tenant
        ]);

        if ($request->has('photo') && !is_file($request->photo)) {
            $data['photo'] = $request->input('photo');
        }

        if ($request->hasFile('photo')) {
            if ($product->photo && Storage::disk('public')->exists($product->photo)) {
                Storage::disk('public')->delete($product->photo);
            }
            $data['photo'] = $request->file('photo')->store('products', 'public');
        }

        $product->update($data);

        $product = $product->fresh()->load('store');

        $productData = array_merge($product->toArray(), [
            'photo' => $product->photo_url,
            'photo_url' => $product->photo_url,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Produk menu berhasil diperbarui!',
                'product' => $productData,
            ]);
        }

        return redirect()->route('admin.produk')->with('success', 'Produk menu berhasil diperbarui!');
    }

    public function destroy(Product $product): JsonResponse|RedirectResponse
    {
        if (!$product->store->event->is_active) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat menghapus produk karena event sudah inaktif.'], 403);
        }

        $product->delete();

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.produk')->with('success', 'Produk berhasil dihapus.');
    }
}
