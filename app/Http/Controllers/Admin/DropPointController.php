<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\DropPoint;
use Illuminate\Http\Request;

/**
 * Modul 3 — Drop Point Management (admin).
 */
class DropPointController extends Controller
{
    /**
     * Daftar drop point + pencarian + peta sebaran.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $dropPoints = DropPoint::with('officers')
            ->withCount('deposits')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.drop-points.index', compact('dropPoints', 'search'));
    }

    public function create()
    {
        return view('admin.drop-points.create', ['dropPoint' => new DropPoint(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $dropPoint = DropPoint::create($this->validateDropPoint($request));

        return redirect()->route('admin.drop-points.show', $dropPoint)
            ->with('success', "Drop point {$dropPoint->name} berhasil ditambahkan.");
    }

    /**
     * Detail drop point: lokasi, petugas, dan ringkasan setoran.
     */
    public function show(DropPoint $dropPoint)
    {
        $dropPoint->load('officers')->loadCount('deposits');

        $collected = $dropPoint->deposits()->where('status', Deposit::STATUS_COLLECTED);
        $summary = [
            'collected_count' => (clone $collected)->count(),
            'collected_weight' => (clone $collected)->sum('total_weight'),
            'total_weight' => $dropPoint->deposits()->sum('total_weight'),
        ];

        $latestDeposits = $dropPoint->deposits()
            ->with(['user', 'receiver'])
            ->latest('deposit_date')
            ->take(5)
            ->get();

        return view('admin.drop-points.show', compact('dropPoint', 'summary', 'latestDeposits'));
    }

    public function edit(DropPoint $dropPoint)
    {
        return view('admin.drop-points.edit', compact('dropPoint'));
    }

    public function update(Request $request, DropPoint $dropPoint)
    {
        $dropPoint->update($this->validateDropPoint($request));

        return redirect()->route('admin.drop-points.show', $dropPoint)
            ->with('success', "Drop point {$dropPoint->name} berhasil diperbarui.");
    }

    /**
     * Drop point yang sudah punya setoran tidak boleh dihapus (riwayat harus utuh).
     */
    public function destroy(DropPoint $dropPoint)
    {
        if ($dropPoint->deposits()->exists()) {
            return back()->with('error', "Drop point {$dropPoint->name} sudah memiliki setoran sehingga tidak dapat dihapus. Nonaktifkan saja.");
        }

        $dropPoint->delete();

        return redirect()->route('admin.drop-points.index')
            ->with('success', "Drop point {$dropPoint->name} berhasil dihapus.");
    }

    /**
     * Validasi yang sama untuk tambah dan ubah.
     */
    private function validateDropPoint(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90|required_with:longitude',
            'longitude' => 'nullable|numeric|between:-180,180|required_with:latitude',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Nama drop point wajib diisi.',
            'name.max' => 'Nama drop point maksimal 100 karakter.',
            'address.required' => 'Alamat wajib diisi.',
            'address.max' => 'Alamat maksimal 255 karakter.',
            'latitude.numeric' => 'Latitude harus berupa angka.',
            'latitude.between' => 'Latitude harus di antara -90 dan 90.',
            'latitude.required_with' => 'Latitude wajib diisi jika longitude diisi.',
            'longitude.numeric' => 'Longitude harus berupa angka.',
            'longitude.between' => 'Longitude harus di antara -180 dan 180.',
            'longitude.required_with' => 'Longitude wajib diisi jika latitude diisi.',
        ]);

        // Checkbox tidak terkirim saat tidak dicentang
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
