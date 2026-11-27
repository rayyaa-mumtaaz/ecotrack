<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use Illuminate\Http\Request;

/**
 * Modul 4 — Riwayat setoran milik user (penyetor) yang sedang login.
 */
class DepositHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $deposits = $user->deposits()
            ->with(['dropPoint', 'receiver'])
            ->withCount('items')
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest('deposit_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $summary = [
            'count' => $user->deposits()->count(),
            'weight' => $user->deposits()->sum('total_weight'),
        ];

        return view('user.deposits.index', compact('deposits', 'summary'));
    }

    public function show(Request $request, Deposit $deposit)
    {
        // User hanya boleh melihat setorannya sendiri
        abort_unless($deposit->user_id === $request->user()->id, 403, 'Setoran ini bukan milik Anda.');

        $deposit->load(['dropPoint', 'receiver', 'items.category', 'pickup.partner']);

        return view('user.deposits.show', compact('deposit'));
    }
}
