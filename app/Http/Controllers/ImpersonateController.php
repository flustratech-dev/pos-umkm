<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    /**
     * Start impersonating a store owner.
     */
    public function impersonate(Request $request, Store $store): RedirectResponse
    {
        $currentUser = Auth::user();

        // Only Admin or SuperAdmin can impersonate
        if (!$currentUser || (!$currentUser->isAdmin() && !$currentUser->isSuperAdmin())) {
            abort(403, 'Akses ditolak. Hanya panitia EO / Admin yang dapat menggunakan mode inspeksi.');
        }

        $owner = $store->owner;
        if (!$owner) {
            return redirect()->back()->with('error', 'Warung ini tidak memiliki akun pemilik terdaftar.');
        }

        // Save original admin details into session
        session()->put('impersonator_id', $currentUser->id);
        session()->put('impersonator_name', $currentUser->name);
        session()->put('impersonator_role', $currentUser->role);

        // Ensure owner has store_id pointing to this store
        if ($owner->store_id !== $store->id) {
            $owner->update(['store_id' => $store->id]);
        }

        // Login as store owner
        Auth::login($owner);

        return redirect()->route('user.kasir')->with('success', 'Masuk ke Mode Inspeksi sebagai stand ' . $store->name . ' (' . $owner->name . '). Anda dapat menguji kasir, menambah menu, atau mengecek laporan.');
    }

    /**
     * Stop impersonating and return to original admin.
     */
    public function leave(Request $request): RedirectResponse
    {
        if (!session()->has('impersonator_id')) {
            return redirect()->route('login');
        }

        $adminId = session()->get('impersonator_id');
        $admin = User::find($adminId);

        // Clear impersonation session keys
        session()->forget(['impersonator_id', 'impersonator_name', 'impersonator_role']);

        if ($admin) {
            Auth::login($admin);
            $redirectRoute = $admin->isSuperAdmin() ? 'superadmin.dashboard' : 'admin.warung';
            return redirect()->route($redirectRoute)->with('success', 'Berhasil keluar dari mode inspeksi dan kembali ke Akun Admin (' . $admin->name . ').');
        }

        return redirect()->route('login');
    }
}
