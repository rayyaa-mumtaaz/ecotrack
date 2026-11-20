<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\DropPoint;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Halaman ringkasan admin. Semua angka diambil dari database.
     * Statistik lengkap (rekap kategori, dll.) ditambahkan pada Stage 6.
     */
    public function index()
    {
        $stats = [
            'total_deposits' => Deposit::count(),
            'total_weight' => Deposit::sum('total_weight'),
            'total_users' => User::where('role', User::ROLE_USER)->count(),
            'active_drop_points' => DropPoint::where('is_active', true)->count(),
        ];

        $latestDeposits = Deposit::with(['user', 'dropPoint'])
            ->latest('deposit_date')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'latestDeposits'));
    }
}
