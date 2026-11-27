<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\DropPoint;
use Illuminate\Http\Request;

/**
 * Modul 3 — Peta drop point (SIG) dengan Leaflet + OpenStreetMap.
 */
class DropPointMapController extends Controller
{
    /**
     * Halaman peta drop point untuk user (penyetor).
     */
    public function index()
    {
        return view('user.drop-points.index');
    }

    /**
     * Data peta dalam format JSON. Dipanggil JavaScript dengan fetch() tiap 30 detik
     * agar peta selalu menampilkan data terbaru tanpa memuat ulang halaman.
     * User hanya melihat drop point aktif; admin melihat semua.
     */
    public function data(Request $request)
    {
        $dropPoints = DropPoint::with('officers:id,name,drop_point_id')
            ->withCount(['deposits as collected_count' => function ($query) {
                $query->where('status', Deposit::STATUS_COLLECTED);
            }])
            ->withSum(['deposits as collected_weight' => function ($query) {
                $query->where('status', Deposit::STATUS_COLLECTED);
            }], 'total_weight')
            ->when(! $request->user()->isStaff(), fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->get()
            ->map(fn (DropPoint $dropPoint) => [
                'id' => $dropPoint->id,
                'name' => $dropPoint->name,
                'address' => $dropPoint->address,
                'description' => $dropPoint->description,
                'latitude' => $dropPoint->latitude,
                'longitude' => $dropPoint->longitude,
                'is_active' => $dropPoint->is_active,
                'officers' => $dropPoint->officers->pluck('name'),
                'collected_count' => $dropPoint->collected_count,
                'collected_weight' => round((float) $dropPoint->collected_weight, 2),
            ]);

        return response()->json([
            'updated_at' => now()->format('H:i:s'),
            'drop_points' => $dropPoints,
        ]);
    }
}
