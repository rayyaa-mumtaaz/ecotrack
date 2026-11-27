<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\DepositItem;
use App\Models\DropPoint;
use App\Models\Pickup;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Halaman ringkasan admin. Semua angka diambil dari database.
     */
    public function index()
    {
        $stats = [
            'total_deposits' => Deposit::count(),
            'total_weight' => Deposit::sum('total_weight'),
            'total_users' => User::where('role', User::ROLE_USER)->count(),
            'active_drop_points' => DropPoint::where('is_active', true)->count(),
            'waiting_deposits' => Deposit::where('status', Deposit::STATUS_COLLECTED)->count(),
            'waiting_weight' => Deposit::where('status', Deposit::STATUS_COLLECTED)->sum('total_weight'),
            'total_pickups' => Pickup::count(),
        ];

        // Ringkasan per kategori: jumlah barang dan berat
        $categorySummary = DepositItem::with('category')
            ->selectRaw('category_id, SUM(quantity) as quantity, SUM(weight) as weight')
            ->groupBy('category_id')
            ->get()
            ->sortByDesc('weight')
            ->values();

        $latestDeposits = Deposit::with(['user', 'dropPoint', 'receiver'])
            ->latest('deposit_date')
            ->latest('id')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'categorySummary', 'latestDeposits'));
    }
}
