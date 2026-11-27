<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DropPoint;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Modul 1 — User Management (admin).
 * Akun user (penyetor) dibuat oleh admin; tidak ada registrasi publik.
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('dropPoint')
            ->withCount(['deposits', 'receivedDeposits'])
            ->when($request->query('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->query('role'), fn ($query, $role) => $query->where('role', $role))
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create', [
            'user' => new User(['role' => User::ROLE_USER]),
            'dropPoints' => $this->dropPointOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $user = User::create($this->validateUser($request));

        return redirect()->route('admin.users.index')
            ->with('success', "Pengguna {$user->name} berhasil ditambahkan.");
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user,
            'dropPoints' => $this->dropPointOptions(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        // Admin tidak boleh menurunkan role dirinya sendiri (agar tidak terkunci dari panel admin)
        if ($user->is($request->user()) && $request->input('role') !== User::ROLE_ADMIN) {
            return back()->withInput()->withErrors(['role' => 'Anda tidak dapat mengubah role akun Anda sendiri.']);
        }

        $user->update($this->validateUser($request, $user));

        return redirect()->route('admin.users.index')
            ->with('success', "Pengguna {$user->name} berhasil diperbarui.");
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Riwayat setoran harus tetap utuh (sebagai penyetor maupun penerima)
        if ($user->deposits()->exists() || $user->receivedDeposits()->exists()) {
            return back()->with('error', "Pengguna {$user->name} sudah memiliki riwayat setoran sehingga tidak dapat dihapus.");
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "Pengguna {$user->name} berhasil dihapus.");
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            // Password wajib saat tambah; saat ubah boleh dikosongkan (tidak diganti)
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_USER])],
            'drop_point_id' => 'nullable|exists:drop_points,id',
        ]);

        if (empty($data['password'])) {
            unset($data['password']); // tidak mengganti password lama
        }

        // Drop point tugas hanya untuk admin/petugas
        if ($data['role'] !== User::ROLE_ADMIN) {
            $data['drop_point_id'] = null;
        }

        return $data;
    }

    private function dropPointOptions()
    {
        return DropPoint::orderBy('name')->pluck('name', 'id');
    }
}
