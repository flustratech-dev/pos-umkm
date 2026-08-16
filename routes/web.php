<?php

use App\Http\Controllers\Admin\CashVerificationController as AdminCashVerificationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\EventDetailController as AdminEventDetailController;
use App\Http\Controllers\Admin\GuideController as AdminGuideController;
use App\Http\Controllers\Admin\HelpdeskController as AdminHelpdeskController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\QrisVerificationController as AdminQrisVerificationController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\StoreController as AdminStoreController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\TenantAccessController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\EventController as SuperAdminEventController;
use App\Http\Controllers\SuperAdmin\PlatformReportController as SuperAdminPlatformReportController;
use App\Http\Controllers\User\GuideController as UserGuideController;
use App\Http\Controllers\User\HelpdeskController as UserHelpdeskController;
use App\Http\Controllers\User\KasirController as UserKasirController;
use App\Http\Controllers\User\ProductController as UserProductController;
use App\Http\Controllers\User\ReportController as UserReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->isSuperAdmin()) return redirect()->route('superadmin.dashboard');
        if ($user->isAdmin()) return redirect()->route('admin.dashboard');
        return redirect()->route('user.kasir');
    }
    return redirect()->route('login');
});

// Authentication Routes (Admin & Superadmin only — no self-registration)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Tenant UUID Access (Public — no login required)
Route::get('/tenda/{uuid}', [TenantAccessController::class, 'access'])->name('tenant.access');

// Global Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});
// Shared Receipts / Public Print Routes
Route::get('/receipt/{transaction}', [ReceiptController::class, 'show'])->name('receipt.show');
Route::get('/receipt/{transaction}/print', [ReceiptController::class, 'print'])->name('receipt.print');

// 1. User (Pemilik Warung) Routes
Route::prefix('user')->name('user.')->middleware(['auth', 'role:user'])->group(function () {
    Route::get('/kasir', [UserKasirController::class, 'index'])->name('kasir');
    Route::post('/kasir/checkout-cash', [UserKasirController::class, 'checkoutCash'])->name('kasir.checkout-cash');
    Route::post('/kasir/checkout-qris', [UserKasirController::class, 'checkoutQris'])->name('kasir.checkout-qris');

    Route::get('/produk', [UserProductController::class, 'index'])->name('produk');
    Route::post('/produk', [UserProductController::class, 'store'])->name('produk.store');
    Route::match(['put', 'post'], '/produk/{product}', [UserProductController::class, 'update'])->name('produk.update');
    Route::delete('/produk/{product}', [UserProductController::class, 'destroy'])->name('produk.destroy');

    Route::get('/laporan', [UserReportController::class, 'index'])->name('laporan');
    Route::get('/laporan/pdf', [UserReportController::class, 'downloadPdf'])->name('laporan.pdf');

    Route::get('/helpdesk', [UserHelpdeskController::class, 'index'])->name('helpdesk');
    Route::post('/helpdesk', [UserHelpdeskController::class, 'store'])->name('helpdesk.store');
    Route::post('/helpdesk/{ticket}/reply', [UserHelpdeskController::class, 'reply'])->name('helpdesk.reply');

    Route::get('/panduan', [UserGuideController::class, 'index'])->name('panduan');
    Route::post('/switch-store', [UserKasirController::class, 'switchStore'])->name('switch-store');
});

// 2. Admin (EO - Event Organizer) Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/events', [AdminEventController::class, 'index'])->name('events.index');
    Route::post('/events', [AdminEventController::class, 'store'])->name('events.store');
    Route::match(['put', 'post'], '/events/{event}', [AdminEventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [AdminEventController::class, 'destroy'])->name('events.destroy');
    Route::post('/events/{event}/activate', [AdminEventController::class, 'activate'])->name('events.activate');

    // Event Detail + Tenant Registration
    Route::get('/events/{event}/detail', [AdminEventDetailController::class, 'show'])->name('events.detail');
    Route::post('/events/{event}/register-tenant', [AdminEventDetailController::class, 'registerTenant'])->name('events.register-tenant');
    Route::delete('/events/{event}/tenants/{store}', [AdminEventDetailController::class, 'removeTenant'])->name('events.remove-tenant');
    Route::post('/events/{event}/tenants/{store}/regenerate-link', [AdminEventDetailController::class, 'regenerateLink'])->name('events.regenerate-link');

    // Verifikasi QRIS
    Route::get('/verifikasi-qris', [AdminQrisVerificationController::class, 'index'])->name('verifikasi-qris.index');
    Route::post('/verifikasi-qris/{transaction}/approve', [AdminQrisVerificationController::class, 'approve'])->name('verifikasi-qris.approve');
    Route::post('/verifikasi-qris/{transaction}/reject', [AdminQrisVerificationController::class, 'reject'])->name('verifikasi-qris.reject');

    // Verifikasi Cash
    Route::get('/verifikasi-cash', [AdminCashVerificationController::class, 'index'])->name('verifikasi-cash.index');
    Route::post('/verifikasi-cash/{transaction}/confirm', [AdminCashVerificationController::class, 'confirm'])->name('verifikasi-cash.confirm');

    Route::post('/transaksi/{transaction}/cancel', [AdminTransactionController::class, 'cancel'])->name('transaksi.cancel');

    Route::get('/produk', [AdminProductController::class, 'index'])->name('produk');
    Route::get('/warung', [AdminStoreController::class, 'index'])->name('warung');
    Route::get('/warung/{store}', [AdminStoreController::class, 'show'])->name('warung.show');

    Route::get('/laporan', [AdminReportController::class, 'index'])->name('laporan');
    Route::get('/laporan/pdf', [AdminReportController::class, 'downloadPdf'])->name('laporan.pdf');

    Route::get('/helpdesk', [AdminHelpdeskController::class, 'index'])->name('helpdesk');
    Route::post('/helpdesk/{ticket}/status', [AdminHelpdeskController::class, 'updateStatus'])->name('helpdesk.status');
    Route::post('/helpdesk/{ticket}/reply', [AdminHelpdeskController::class, 'reply'])->name('helpdesk.reply');

    Route::get('/panduan', [AdminGuideController::class, 'index'])->name('panduan');
    Route::post('/warung/pull', [AdminStoreController::class, 'pull'])->name('warung.pull');
});
// 3. Super Admin Routes (Full System Visibility)
Route::prefix('superadmin')->name('superadmin.')->middleware(['auth', 'role:superadmin'])->group(function () {
    Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/events', [SuperAdminEventController::class, 'index'])->name('events.index');
    Route::post('/events', [SuperAdminEventController::class, 'store'])->name('events.store');
    Route::match(['put', 'post'], '/events/{event}', [SuperAdminEventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [SuperAdminEventController::class, 'destroy'])->name('events.destroy');
    Route::post('/events/{event}/activate', [SuperAdminEventController::class, 'activate'])->name('events.activate');

    Route::get('/laporan', [SuperAdminPlatformReportController::class, 'index'])->name('laporan');
    Route::get('/laporan/pdf', [SuperAdminPlatformReportController::class, 'downloadPdf'])->name('laporan.pdf');

    Route::get('/verifikasi-qris', [AdminQrisVerificationController::class, 'index'])->name('verifikasi-qris');
    Route::get('/verifikasi-cash', [AdminCashVerificationController::class, 'index'])->name('verifikasi-cash');
    Route::get('/produk', [AdminProductController::class, 'index'])->name('produk');
    Route::get('/warung', [AdminStoreController::class, 'index'])->name('warung');
    Route::get('/helpdesk', [AdminHelpdeskController::class, 'index'])->name('helpdesk');
    Route::get('/kasir', [UserKasirController::class, 'index'])->name('kasir');
    Route::get('/panduan', [AdminGuideController::class, 'index'])->name('panduan');
});
