<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — POS Kasir UMKM Event (Fase 1 Frontend)
|--------------------------------------------------------------------------
| Seluruh rute frontend terhubung ke view Blade interaktif
| didukung Alpine.js reactive store & mock state management.
*/

// Home / Root Landing -> Redirect to default active terminal
Route::get('/', function () {
    return redirect('/user/kasir');
});

// Authentication Routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// 1. User (Pemilik Warung) Routes
Route::prefix('user')->name('user.')->group(function () {
    Route::get('/kasir', function () {
        return view('user.kasir');
    })->name('kasir');

    Route::get('/produk', function () {
        return view('user.produk');
    })->name('produk');

    Route::get('/laporan', function () {
        return view('user.laporan');
    })->name('laporan');

    Route::get('/helpdesk', function () {
        return view('user.helpdesk');
    })->name('helpdesk');

    Route::get('/panduan', function () {
        return view('user.guide');
    })->name('panduan');
});

// 2. Admin (EO - Event Organizer) Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::get('/events', function () {
        return view('superadmin.events');
    })->name('events');

    Route::get('/verifikasi-qris', function () {
        return view('admin.verifikasi-qris');
    })->name('verifikasi-qris');

    Route::get('/produk', function () {
        return view('admin.produk');
    })->name('produk');

    Route::get('/warung', function () {
        return view('admin.warung');
    })->name('warung');

    Route::get('/laporan', function () {
        return view('admin.laporan');
    })->name('laporan');

    Route::get('/helpdesk', function () {
        return view('admin.helpdesk');
    })->name('helpdesk');

    Route::get('/panduan', function () {
        return view('admin.guide');
    })->name('panduan');
});

// 3. Super Admin Routes (Full System Visibility sesuai PRD Section 2)
Route::prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('superadmin.dashboard');
    })->name('dashboard');

    Route::get('/events', function () {
        return view('superadmin.events');
    })->name('events');

    Route::get('/laporan', function () {
        return view('superadmin.laporan');
    })->name('laporan');

    Route::get('/verifikasi-qris', function () {
        return view('admin.verifikasi-qris');
    })->name('verifikasi-qris');

    Route::get('/produk', function () {
        return view('admin.produk');
    })->name('produk');

    Route::get('/warung', function () {
        return view('admin.warung');
    })->name('warung');

    Route::get('/helpdesk', function () {
        return view('admin.helpdesk');
    })->name('helpdesk');

    Route::get('/kasir', function () {
        return view('user.kasir');
    })->name('kasir');

    Route::get('/panduan', function () {
        return view('admin.guide');
    })->name('panduan');
});
