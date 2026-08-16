<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EventDetailController extends Controller
{
    /**
     * Show event detail with registered tenants and registration form.
     */
    public function show(Event $event): View
    {
        $tenants = Store::where('event_id', $event->id)
            ->with('owner')
            ->orderBy('booth_number')
            ->get();

        return view('admin.event-detail', compact('event', 'tenants'));
    }

    /**
     * Register a new tenant for this event.
     * Creates User + Store with auto-generated UUID access link.
     */
    public function registerTenant(Request $request, Event $event): JsonResponse|RedirectResponse
    {
        // Support both booth_code and booth_number field names
        $boothCode = $request->input('booth_code') ?: $request->input('booth_number');
        $request->merge(['booth_code' => $boothCode, 'booth_number' => $boothCode]);

        $request->validate([
            'owner_name' => ['required', 'string', 'max:255'],
            'store_name' => ['required', 'string', 'max:255'],
            'booth_code' => ['required', 'string', 'max:50'],
        ], [
            'owner_name.required' => 'Nama pelaku usaha wajib diisi.',
            'store_name.required' => 'Nama warung wajib diisi.',
            'booth_code.required' => 'Kode tenda wajib diisi.',
        ]);

        try {
            $result = DB::transaction(function () use ($request, $event, $boothCode) {
                $uuid = (string) Str::uuid();

                // Generate unique username from booth code
                $baseUsername = 'tenda-' . Str::slug($boothCode);
                $username = $baseUsername;
                $counter = 1;
                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . '-' . $counter;
                    $counter++;
                }

                // Generate placeholder email
                $email = $username . '-' . substr($uuid, 0, 8) . '@tenant.local';

                // Create user without real password (access via UUID link)
                $user = User::create([
                    'name' => $request->owner_name,
                    'username' => $username,
                    'email' => $email,
                    'phone' => $request->phone ?? null,
                    'role' => 'user',
                    'password' => Hash::make(Str::random(32)),
                ]);

                // Create store with UUID access
                $store = Store::create([
                    'event_id' => $event->id,
                    'owner_id' => $user->id,
                    'name' => $request->store_name,
                    'booth_number' => $boothCode,
                    'access_uuid' => $uuid,
                    'category' => $request->category ?? 'Makanan & Minuman',
                    'is_active' => true,
                ]);

                // Link user to store
                $user->update(['store_id' => $store->id]);

                // Generate access URL
                $accessUrl = route('tenant.access', ['uuid' => $uuid]);

                return [
                    'user' => $user,
                    'store' => $store,
                    'access_url' => $accessUrl,
                ];
            });

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Tenant '{$result['store']->name}' berhasil didaftarkan!",
                    'store' => $result['store']->load('owner'),
                    'access_url' => $result['access_url'],
                ]);
            }

            return redirect()->route('admin.events.detail', $event)
                ->with('success', "Tenant '{$result['store']->name}' berhasil didaftarkan!");
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mendaftarkan tenant: ' . $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', 'Gagal mendaftarkan tenant: ' . $e->getMessage());
        }
    }

    /**
     * Remove a tenant from the event.
     */
    public function removeTenant(Request $request, Event $event, Store $store): JsonResponse|RedirectResponse
    {
        if ($store->event_id !== $event->id) {
            $msg = 'Tenant ini tidak terdaftar di event ini.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        // Check for existing paid transactions
        $paidCount = $store->transactions()->where('status', 'paid')->count();
        if ($paidCount > 0) {
            $msg = "Tidak dapat menghapus tenant yang sudah memiliki {$paidCount} transaksi paid. Nonaktifkan saja.";
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        $storeName = $store->name;

        DB::transaction(function () use ($store) {
            // Delete associated user
            if ($store->owner) {
                $store->owner->delete();
            }
            $store->delete();
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Tenant '{$storeName}' berhasil dihapus.",
            ]);
        }

        return redirect()->route('admin.events.detail', $event)
            ->with('success', "Tenant '{$storeName}' berhasil dihapus.");
    }

    /**
     * Regenerate UUID access link for a tenant.
     */
    public function regenerateLink(Request $request, Event $event, Store $store): JsonResponse
    {
        if ($store->event_id !== $event->id) {
            return response()->json(['success' => false, 'message' => 'Tenant tidak terdaftar di event ini.'], 422);
        }

        $newUuid = (string) Str::uuid();
        $store->update(['access_uuid' => $newUuid]);

        return response()->json([
            'success' => true,
            'message' => 'Link akses berhasil di-regenerate.',
            'access_url' => route('tenant.access', ['uuid' => $newUuid]),
        ]);
    }
}
