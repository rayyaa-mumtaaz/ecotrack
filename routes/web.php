<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepositController;
use App\Http\Controllers\Admin\DropPointController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\PickupController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DropPointMapController;
use App\Http\Controllers\User\CategoryController as UserCategoryController;
use App\Http\Controllers\User\DepositHistoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — EcoTrack
|--------------------------------------------------------------------------
| guest      : hanya untuk yang belum login
| auth       : harus login
| idle       : logout otomatis jika lama tidak aktif (middleware LogoutIfIdle)
| role:superadmin       : khusus superadmin (koordinator)
| role:superadmin,admin : superadmin atau petugas drop point (middleware CheckUserRole)
| role:user  : khusus user / penyetor
*/

Route::redirect('/', '/login');

// Modul 1 — Login (tamu)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware(['auth', 'idle'])->group(function () {
    // Modul 1 — Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Modul 1 — Perpanjang sesi (dipanggil tombol "Tetap masuk" via JavaScript)
    Route::get('/session/ping', fn () => response()->noContent())->name('session.ping');

    // Modul 3 — Data peta drop point (JSON, dipanggil fetch() tiap 30 detik; admin & user)
    Route::get('/drop-points/map-data', [DropPointMapController::class, 'data'])->name('drop-points.map-data');

    // Halaman admin: superadmin (koordinator) dan petugas drop point
    Route::middleware('role:superadmin,admin')->prefix('admin')->name('admin.')->group(function () {
        // Master data: tambah, ubah, hapus hanya superadmin.
        // Didaftarkan sebelum route detail agar URL /create tidak dianggap sebagai {id}.
        Route::middleware('role:superadmin')->group(function () {
            Route::resource('categories', CategoryController::class)->except(['index', 'show']); // Modul 2
            Route::resource('drop-points', DropPointController::class)->except(['index', 'show']); // Modul 3
            Route::resource('partners', PartnerController::class)->except(['index', 'show']); // Modul 6
        });

        // Master data: daftar dan detail boleh dilihat petugas
        Route::resource('categories', CategoryController::class)->only('index');
        Route::resource('drop-points', DropPointController::class)->only(['index', 'show']);
        Route::resource('partners', PartnerController::class)->only(['index', 'show']);

        // Modul 5 — Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Modul 1 — CRUD pengguna (petugas hanya mengelola akun penyetor; diatur di controller)
        Route::resource('users', UserController::class)->except('show');

        // Modul 4 — CRUD setoran + ubah status (petugas hanya drop point-nya; diatur di controller)
        Route::resource('deposits', DepositController::class);
        Route::patch('/deposits/{deposit}/status', [DepositController::class, 'updateStatus'])->name('deposits.status');

        // Modul 5 — Pengangkutan + tandai diserahkan ke mitra
        Route::resource('pickups', PickupController::class);
        Route::patch('/pickups/{pickup}/complete', [PickupController::class, 'complete'])->name('pickups.complete');

        // Modul 5 — Laporan + ekspor (file dibuat saat diunduh, tidak disimpan di server)
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
        Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    });

    // Halaman user / penyetor
    Route::middleware('role:user')->group(function () {
        // Modul 4 — Riwayat setoran milik sendiri
        Route::get('/my-deposits', [DepositHistoryController::class, 'index'])->name('my-deposits.index');
        Route::get('/my-deposits/{deposit}', [DepositHistoryController::class, 'show'])->name('my-deposits.show');

        // Modul 2 — Daftar kategori
        Route::get('/categories', [UserCategoryController::class, 'index'])->name('categories.index');

        // Modul 3 — Peta drop point
        Route::get('/drop-points', [DropPointMapController::class, 'index'])->name('drop-points.index');
    });
});
