<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\DropPoint;
use App\Models\Partner;
use App\Models\Pickup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Modul 5 — Pickup Management: pengangkutan setoran ke mitra.
 * Alur status setoran: Terkumpul -> (dimasukkan ke pengangkutan) Diangkut -> (tandai selesai) Selesai.
 * Petugas hanya melihat pengangkutan yang memuat setoran drop point-nya, dan hanya dapat
 * mengubah/membatalkan/menyelesaikan pengangkutan yang seluruh setorannya dari drop point-nya.
 */
class PickupController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $pickups = Pickup::with('partner')
            ->when($user->isLimitedToDropPoint(), fn ($query) => $query->whereHas('deposits', fn ($query) => $query->where('drop_point_id', $user->drop_point_id)))
            ->withCount(['deposits', 'deposits as completed_count' => function ($query) {
                $query->where('status', Deposit::STATUS_COMPLETED);
            }])
            ->when($request->query('search'), fn ($query, $search) => $query->where('pickup_number', 'like', "%{$search}%"))
            ->when($request->query('partner_id'), fn ($query, $id) => $query->where('partner_id', $id))
            ->latest('pickup_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $partners = Partner::orderBy('name')->pluck('name', 'id');
        $waitingCount = Deposit::where('status', Deposit::STATUS_COLLECTED)
            ->when($user->isLimitedToDropPoint(), fn ($query) => $query->where('drop_point_id', $user->drop_point_id))
            ->count();

        return view('admin.pickups.index', compact('pickups', 'partners', 'waitingCount'));
    }

    public function create(Request $request)
    {
        $user = $request->user();

        if ($user->isLimitedToDropPoint() && $user->drop_point_id === null) {
            return redirect()->route('admin.pickups.index')
                ->with('error', 'Anda belum ditugaskan ke drop point. Hubungi superadmin.');
        }

        return view('admin.pickups.create', [
            'pickup' => new Pickup(['pickup_date' => now()->toDateString()]),
            'partners' => $this->partnerOptions(),
            'availableDeposits' => $this->availableDeposits($user),
            'dropPoints' => DropPoint::when($user->isLimitedToDropPoint(), fn ($query) => $query->where('id', $user->drop_point_id))
                ->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    /**
     * Simpan pengangkutan dan ubah status setoran terpilih menjadi Diangkut,
     * semuanya dalam satu transaksi database.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'pickup_date' => 'required|date|before_or_equal:today',
            'partner_id' => ['required', Rule::exists('partners', 'id')->where('is_active', true)],
            'notes' => 'nullable|string|max:1000',
            'deposit_ids' => 'required|array|min:1',
            // Hanya setoran Terkumpul yang belum masuk pengangkutan lain
            // Petugas hanya boleh memilih setoran dari drop point-nya
            'deposit_ids.*' => ['integer', 'distinct', Rule::exists('deposits', 'id')->where(function ($query) use ($user) {
                $query->where('status', Deposit::STATUS_COLLECTED)->whereNull('pickup_id');

                if ($user->isLimitedToDropPoint()) {
                    $query->where('drop_point_id', $user->drop_point_id);
                }
            })],
        ], [
            'deposit_ids.required' => 'Pilih minimal satu setoran yang diangkut.',
            'deposit_ids.*.exists' => 'Ada setoran yang sudah diangkut atau tidak valid. Muat ulang halaman.',
            'pickup_date.before_or_equal' => 'Tanggal pengangkutan tidak boleh melebihi hari ini.',
        ], [
            'pickup_date' => 'tanggal pengangkutan',
            'partner_id' => 'mitra',
        ]);

        $pickup = DB::transaction(function () use ($data) {
            $pickup = Pickup::create([
                'pickup_number' => Pickup::generateNumber($data['pickup_date']),
                'pickup_date' => $data['pickup_date'],
                'partner_id' => $data['partner_id'],
                'notes' => $data['notes'] ?? null,
            ]);

            Deposit::whereIn('id', $data['deposit_ids'])->update([
                'pickup_id' => $pickup->id,
                'status' => Deposit::STATUS_SHIPPED,
            ]);

            $pickup->recalculateTotalWeight();

            return $pickup;
        });

        return redirect()->route('admin.pickups.show', $pickup)
            ->with('success', "Pengangkutan {$pickup->pickup_number} dicatat. " . count($data['deposit_ids']) . ' setoran berstatus Diangkut.');
    }

    public function show(Request $request, Pickup $pickup)
    {
        $user = $request->user();

        // Petugas boleh melihat jika ada setoran dari drop point-nya
        if ($user->isLimitedToDropPoint() && ! $pickup->deposits()->where('drop_point_id', $user->drop_point_id)->exists()) {
            abort(403, 'Pengangkutan ini tidak memuat setoran dari drop point Anda.');
        }

        $canManage = $this->canManage($user, $pickup);

        $pickup->load(['partner', 'deposits' => fn ($query) => $query->with(['user', 'dropPoint', 'receiver'])->withCount('items')]);

        return view('admin.pickups.show', compact('pickup', 'canManage'));
    }

    /**
     * Ubah tanggal, mitra, dan catatan. Daftar setoran tidak diubah di sini.
     */
    public function edit(Request $request, Pickup $pickup)
    {
        $this->authorizeManage($request, $pickup);

        return view('admin.pickups.edit', [
            'pickup' => $pickup,
            'partners' => $this->partnerOptions($pickup->partner_id),
        ]);
    }

    public function update(Request $request, Pickup $pickup)
    {
        $this->authorizeManage($request, $pickup);

        $data = $request->validate([
            'pickup_date' => 'required|date|before_or_equal:today',
            // Mitra harus aktif; mitra lama tetap boleh walau sudah nonaktif
            'partner_id' => ['required', Rule::exists('partners', 'id')->where(function ($query) use ($pickup) {
                $query->where('is_active', true)->orWhere('id', $pickup->partner_id);
            })],
            'notes' => 'nullable|string|max:1000',
        ], [], [
            'pickup_date' => 'tanggal pengangkutan',
            'partner_id' => 'mitra',
        ]);

        $pickup->update($data);

        return redirect()->route('admin.pickups.show', $pickup)
            ->with('success', "Pengangkutan {$pickup->pickup_number} berhasil diperbarui.");
    }

    /**
     * Batalkan pengangkutan: setoran dikembalikan ke status Terkumpul.
     * Tidak boleh jika sudah ada setoran yang Selesai (sudah diserahkan ke mitra).
     */
    public function destroy(Request $request, Pickup $pickup)
    {
        $this->authorizeManage($request, $pickup);

        if ($pickup->deposits()->where('status', Deposit::STATUS_COMPLETED)->exists()) {
            return back()->with('error', "Pengangkutan {$pickup->pickup_number} sudah diserahkan ke mitra sehingga tidak dapat dibatalkan.");
        }

        DB::transaction(function () use ($pickup) {
            $pickup->deposits()->update(['pickup_id' => null, 'status' => Deposit::STATUS_COLLECTED]);
            $pickup->delete();
        });

        return redirect()->route('admin.pickups.index')
            ->with('success', "Pengangkutan {$pickup->pickup_number} dibatalkan. Setoran kembali berstatus Terkumpul.");
    }

    /**
     * Tandai semua setoran dalam pengangkutan sudah diserahkan ke mitra (Selesai).
     */
    public function complete(Request $request, Pickup $pickup)
    {
        $this->authorizeManage($request, $pickup);

        $updated = $pickup->deposits()
            ->where('status', Deposit::STATUS_SHIPPED)
            ->update(['status' => Deposit::STATUS_COMPLETED]);

        if ($updated === 0) {
            return back()->with('error', 'Tidak ada setoran berstatus Diangkut di pengangkutan ini.');
        }

        return back()->with('success', "{$updated} setoran ditandai Selesai (diserahkan ke {$pickup->partner?->name}).");
    }

    /**
     * Superadmin boleh mengelola semua pengangkutan.
     * Petugas hanya jika seluruh setoran di pengangkutan berasal dari drop point-nya.
     */
    private function canManage(User $user, Pickup $pickup): bool
    {
        if (! $user->isLimitedToDropPoint()) {
            return true;
        }

        return $user->drop_point_id !== null
            && $pickup->deposits()->exists()
            && ! $pickup->deposits()->where('drop_point_id', '!=', $user->drop_point_id)->exists();
    }

    private function authorizeManage(Request $request, Pickup $pickup): void
    {
        if (! $this->canManage($request->user(), $pickup)) {
            abort(403, 'Pengangkutan ini memuat setoran dari drop point lain.');
        }
    }

    private function partnerOptions(?int $currentId = null)
    {
        return Partner::where('is_active', true)
            ->when($currentId, fn ($query) => $query->orWhere('id', $currentId))
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn ($partner) => [$partner->id => "{$partner->name} ({$partner->typeLabel()})"]);
    }

    /**
     * Setoran yang siap diangkut: Terkumpul dan belum masuk pengangkutan.
     */
    private function availableDeposits(User $user)
    {
        return Deposit::with(['user', 'dropPoint'])
            ->withCount('items')
            ->when($user->isLimitedToDropPoint(), fn ($query) => $query->where('drop_point_id', $user->drop_point_id))
            ->where('status', Deposit::STATUS_COLLECTED)
            ->whereNull('pickup_id')
            ->orderBy('drop_point_id')
            ->orderBy('deposit_date')
            ->get();
    }
}
