<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $store = $user->store ?: Store::where('owner_id', $user->id)->first();
        
        $products = $store 
            ? Product::where('store_id', $store->id)->latest()->get()
            : collect();

        return view('user.produk', compact('user', 'store', 'products'));
    }

    public function store(ProductRequest $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $store = $user->store ?: Store::where('owner_id', $user->id)->firstOrFail();

        if (!$store->event->is_active) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat menambah produk karena event sudah inaktif.'], 403);
        }

        $photoPath = $request->input('photo');
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('products', 'public');
        }

        $product = Product::create([
            'store_id' => $store->id,
            'title' => $request->title,
            'price' => $request->price,
            'category' => $request->category ?: Product::DEFAULT_CATEGORY,
            'description' => $request->description,
            'photo' => $photoPath,
            'stock_badge' => $request->stock_badge ?: 'Tersedia',
            'is_active' => $request->boolean('is_active', true),
        ]);

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

        return redirect()->route('user.produk')->with('success', 'Produk menu berhasil ditambahkan!');
    }

    public function update(ProductRequest $request, Product $product): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $userStoreId = $user->store_id ?: ($user->store?->id ?: $user->ownedStore?->id);
        if (!$userStoreId || $product->store_id !== $userStoreId) {
            abort(403, 'Akses ditolak.');
        }

        if (!$product->store->event->is_active) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat mengubah produk karena event sudah inaktif.'], 403);
        }

        $data = [
            'title' => $request->title,
            'price' => $request->price,
            'category' => $request->category ?: $product->category,
            'description' => $request->description,
            'stock_badge' => $request->stock_badge ?: $product->stock_badge,
            'is_active' => $request->boolean('is_active', true),
        ];

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

        $productData = array_merge($product->fresh()->toArray(), [
            'photo' => $product->fresh()->photo_url,
            'photo_url' => $product->fresh()->photo_url,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Produk menu berhasil diperbarui!',
                'product' => $productData,
            ]);
        }

        return redirect()->route('user.produk')->with('success', 'Produk menu berhasil diperbarui!');
    }

    public function destroy(Product $product): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $userStoreId = $user->store_id ?: ($user->store?->id ?: $user->ownedStore?->id);
        if (!$userStoreId || $product->store_id !== $userStoreId) {
            abort(403, 'Akses ditolak.');
        }

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

        return redirect()->route('user.produk')->with('success', 'Produk berhasil dihapus.');
    }
}
