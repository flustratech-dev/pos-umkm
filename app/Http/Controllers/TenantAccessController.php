<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class TenantAccessController extends Controller
{
    /**
     * Handle tenant access via UUID link.
     * Auto-login the store owner and redirect to kasir.
     */
    public function access(string $uuid): RedirectResponse|\Illuminate\View\View
    {
        $store = Store::where('access_uuid', $uuid)
            ->with(['event', 'owner'])
            ->first();

        if (!$store) {
            abort(404, 'Link akses tidak valid.');
        }

        // Check if the event is still active
        if (!$store->event || !$store->event->is_active) {
            return view('tenant.event-expired', [
                'store' => $store,
                'event' => $store->event,
            ]);
        }

        // If an Admin or SuperAdmin is currently authenticated in this browser, preserve their session
        if (Auth::check()) {
            $currentUser = Auth::user();
            if ($currentUser->isAdmin() || $currentUser->isSuperAdmin()) {
                session()->put('impersonator_id', $currentUser->id);
                session()->put('impersonator_name', $currentUser->name);
                session()->put('impersonator_role', $currentUser->role);
            }
        }

        // Auto-login as the store owner
        Auth::login($store->owner);
        session(['active_store_id' => $store->id]);

        // Ensure user's store_id is set
        if ($store->owner->store_id !== $store->id) {
            $store->owner->update(['store_id' => $store->id]);
        }

        return redirect()->route('user.kasir');
    }
}
