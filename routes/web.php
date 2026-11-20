<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — EcoTrack
|--------------------------------------------------------------------------
| guest      : hanya untuk yang belum login
| auth       : harus login
| role:admin : khusus admin (middleware CheckUserRole)
| role:user  : khusus user / penyetor
*/

Route::redirect('/', '/login');

// Modul 1 — Login (tamu)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    // Modul 1 — Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Halaman admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        // Modul 5 — Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    // Halaman user / penyetor
    Route::middleware('role:user')->group(function () {
        // Modul 4 — Riwayat setoran (sementara tampilan saja, data diisi pada Stage 5)
        Route::view('/my-deposits', 'user.deposits.index')->name('my-deposits.index');
    });
});
