<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Deposit;
use App\Models\DepositItem;
use App\Models\DropPoint;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Modul 4 — Deposit Management (admin).
 * Satu setoran (deposits) berisi banyak barang (deposit_items).
 */
class DepositController extends Controller
{
    public function index(Request $request)
    {
        $deposits = Deposit::with(['user', 'dropPoint', 'receiver'])
            ->withCount('items')
            ->when($request->query('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('deposit_number', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('drop_point_id'), fn ($query, $id) => $query->where('drop_point_id', $id))
            ->when($request->query('from'), fn ($query, $from) => $query->whereDate('deposit_date', '>=', $from))
            ->when($request->query('to'), fn ($query, $to) => $query->whereDate('deposit_date', '<=', $to))
            ->latest('deposit_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $dropPoints = DropPoint::orderBy('name')->pluck('name', 'id');

        return view('admin.deposits.index', compact('deposits', 'dropPoints'));
    }

    public function create(Request $request)
    {
        // Drop point default = drop point tempat admin yang login bertugas
        $deposit = new Deposit([
            'deposit_date' => now()->toDateString(),
            'drop_point_id' => $request->user()->drop_point_id,
        ]);

        return view('admin.deposits.create', $this->formData($deposit));
    }

    /**
     * Simpan setoran + detail barang dalam satu transaksi database:
     * jika salah satu gagal, semuanya dibatalkan.
     */
    public function store(Request $request)
    {
        $data = $this->validateDeposit($request);

        $deposit = DB::transaction(function () use ($data, $request) {
            $deposit = Deposit::create([
                'deposit_number' => Deposit::generateNumber($data['deposit_date']),
                'user_id' => $data['user_id'],
                'received_by' => $request->user()->id, // penerima = admin yang login, bukan input form
                'drop_point_id' => $data['drop_point_id'],
                'deposit_date' => $data['deposit_date'],
                'notes' => $data['notes'] ?? null,
                'status' => Deposit::STATUS_COLLECTED,
            ]);

            $deposit->items()->createMany($data['items']);
            $deposit->recalculateTotalWeight();

            return $deposit;
        });

        return redirect()->route('admin.deposits.show', $deposit)
            ->with('success', "Setoran {$deposit->deposit_number} berhasil dicatat.");
    }

    public function show(Deposit $deposit)
    {
        $deposit->load(['user', 'dropPoint', 'receiver', 'items.category', 'pickup.partner']);

        return view('admin.deposits.show', compact('deposit'));
    }

    public function edit(Deposit $deposit)
    {
        if ($response = $this->rejectIfNotEditable($deposit)) {
            return $response;
        }

        $deposit->load('items');

        return view('admin.deposits.edit', $this->formData($deposit));
    }

    /**
     * Ubah setoran: detail barang lama diganti dengan daftar barang dari form.
     */
    public function update(Request $request, Deposit $deposit)
    {
        if ($response = $this->rejectIfNotEditable($deposit)) {
            return $response;
        }

        $data = $this->validateDeposit($request, $deposit);

        DB::transaction(function () use ($data, $deposit) {
            $deposit->update([
                'user_id' => $data['user_id'],
                'drop_point_id' => $data['drop_point_id'],
                'deposit_date' => $data['deposit_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            $deposit->items()->delete();
            $deposit->items()->createMany($data['items']);
            $deposit->recalculateTotalWeight();
        });

        return redirect()->route('admin.deposits.show', $deposit)
            ->with('success', "Setoran {$deposit->deposit_number} berhasil diperbarui.");
    }

    public function destroy(Deposit $deposit)
    {
        if ($response = $this->rejectIfNotEditable($deposit)) {
            return $response;
        }

        $deposit->delete(); // detail barang ikut terhapus (cascade)

        return redirect()->route('admin.deposits.index')
            ->with('success', "Setoran {$deposit->deposit_number} berhasil dihapus.");
    }

    /**
     * Perbarui status. Status hanya boleh maju:
     * collected -> shipped (lewat menu Pengangkutan) -> completed.
     */
    public function updateStatus(Request $request, Deposit $deposit)
    {
        $request->validate([
            'status' => ['required', Rule::in(array_keys(Deposit::STATUSES))],
        ]);

        $next = $deposit->nextStatus();

        if ($request->input('status') !== $next) {
            return back()->with('error', 'Status hanya dapat diubah satu langkah maju.');
        }

        if ($next === Deposit::STATUS_SHIPPED) {
            return back()->with('error', 'Status Diangkut diatur melalui pencatatan pengangkutan.');
        }

        $deposit->update(['status' => $next]);

        return back()->with('success', "Status setoran {$deposit->deposit_number} menjadi {$deposit->statusLabel()}.");
    }

    /**
     * Setoran yang sudah diangkut/selesai tidak boleh diubah atau dihapus.
     */
    private function rejectIfNotEditable(Deposit $deposit)
    {
        if ($deposit->status !== Deposit::STATUS_COLLECTED) {
            return redirect()->route('admin.deposits.show', $deposit)
                ->with('error', "Setoran {$deposit->deposit_number} sudah {$deposit->statusLabel()} sehingga tidak dapat diubah atau dihapus.");
        }

        return null;
    }

    private function validateDeposit(Request $request, ?Deposit $deposit = null): array
    {
        return $request->validate([
            'user_id' => ['required', Rule::exists('users', 'id')->where('role', User::ROLE_USER)],
            // Drop point harus aktif; saat ubah, drop point lama tetap boleh walau sudah nonaktif
            'drop_point_id' => ['required', Rule::exists('drop_points', 'id')->where(function ($query) use ($deposit) {
                $query->where('is_active', true)->orWhere('id', $deposit?->drop_point_id);
            })],
            'deposit_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1|max:50',
            'items.*.item_name' => 'required|string|max:100',
            'items.*.category_id' => ['required', Rule::exists('categories', 'id')->where('is_active', true)],
            'items.*.quantity' => 'required|integer|min:1|max:10000',
            'items.*.weight' => 'required|numeric|gt:0|max:10000',
            'items.*.condition' => ['required', Rule::in(array_keys(DepositItem::CONDITIONS))],
            'items.*.notes' => 'nullable|string|max:255',
        ], [
            'items.required' => 'Minimal satu barang harus diisi.',
            'deposit_date.before_or_equal' => 'Tanggal setoran tidak boleh melebihi hari ini.',
        ]);
    }

    /**
     * Data pilihan untuk form tambah/ubah.
     */
    private function formData(Deposit $deposit): array
    {
        return [
            'deposit' => $deposit,
            'users' => User::where('role', User::ROLE_USER)->orderBy('name')->get()
                ->mapWithKeys(fn ($user) => [$user->id => "{$user->name} ({$user->email})"]),
            'dropPoints' => DropPoint::where('is_active', true)
                ->orWhere('id', $deposit->drop_point_id)
                ->orderBy('name')->pluck('name', 'id'),
            'categories' => Category::where('is_active', true)->orderBy('name')->pluck('name', 'id'),
        ];
    }
}
