<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\DepositItem;
use App\Models\DropPoint;
use App\Models\Pickup;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Halaman ringkasan admin. Semua angka diambil dari database.
     * Superadmin melihat semua drop point; petugas hanya drop point tempat ia bertugas.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $limited = $user->isLimitedToDropPoint();

        // Query setoran yang sudah dibatasi sesuai role (dibuat baru setiap dipanggil)
        $deposits = fn () => Deposit::when($limited, fn ($query) => $query->where('drop_point_id', $user->drop_point_id));

        $stats = [
            'total_deposits' => $deposits()->count(),
            'total_weight' => $deposits()->sum('total_weight'),
            'total_users' => User::where('role', User::ROLE_USER)->count(),
            'active_drop_points' => DropPoint::where('is_active', true)->count(),
            'waiting_deposits' => $deposits()->where('status', Deposit::STATUS_COLLECTED)->count(),
            'waiting_weight' => $deposits()->where('status', Deposit::STATUS_COLLECTED)->sum('total_weight'),
            'total_pickups' => Pickup::when($limited, fn ($query) => $query->whereHas('deposits', fn ($query) => $query->where('drop_point_id', $user->drop_point_id)))
                ->count(),
        ];

        // Ringkasan per kategori: jumlah barang dan berat
        $categorySummary = DepositItem::with('category')
            ->when($limited, fn ($query) => $query->whereHas('deposit', fn ($query) => $query->where('drop_point_id', $user->drop_point_id)))
            ->selectRaw('category_id, SUM(quantity) as quantity, SUM(weight) as weight')
            ->groupBy('category_id')
            ->get()
            ->sortByDesc('weight')
            ->values();

        $latestDeposits = $deposits()->with(['user', 'dropPoint', 'receiver'])
            ->latest('deposit_date')
            ->latest('id')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'categorySummary', 'latestDeposits'));
    }
}
