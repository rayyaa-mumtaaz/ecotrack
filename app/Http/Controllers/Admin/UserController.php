<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DropPoint;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Modul 1 — User Management.
 * Superadmin mengelola semua akun (termasuk petugas dan penugasan drop point).
 * Petugas hanya mengelola akun penyetor (role user).
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('dropPoint')
            ->withCount(['deposits', 'receivedDeposits'])
            // Petugas hanya melihat akun penyetor
            ->when(! $request->user()->isSuperadmin(), fn ($query) => $query->where('role', User::ROLE_USER))
            ->when($request->query('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->query('role'), fn ($query, $role) => $query->where('role', $role))
            ->orderByRaw("CASE role WHEN 'superadmin' THEN 1 WHEN 'admin' THEN 2 ELSE 3 END")
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

    public function edit(Request $request, User $user)
    {
        $this->authorizeManage($request->user(), $user);

        return view('admin.users.edit', [
            'user' => $user,
            'dropPoints' => $this->dropPointOptions(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeManage($request->user(), $user);

        // Tidak boleh mengubah role diri sendiri (agar tidak terkunci dari panel admin)
        if ($user->is($request->user()) && $request->has('role') && $request->input('role') !== $user->role) {
            return back()->withInput()->withErrors(['role' => 'Anda tidak dapat mengubah role akun Anda sendiri.']);
        }

        $user->update($this->validateUser($request, $user));

        return redirect()->route('admin.users.index')
            ->with('success', "Pengguna {$user->name} berhasil diperbarui.");
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorizeManage($request->user(), $user);

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

    /**
     * Petugas hanya boleh mengelola akun penyetor. Superadmin boleh semua.
     */
    private function authorizeManage(User $actor, User $target): void
    {
        if (! $actor->isSuperadmin() && $target->role !== User::ROLE_USER) {
            abort(403, 'Hanya superadmin yang dapat mengelola akun petugas dan superadmin.');
        }
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $actor = $request->user();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            // Password wajib saat tambah; saat ubah boleh dikosongkan (tidak diganti)
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ];

        // Role dan drop point tugas hanya dapat diatur superadmin
        if ($actor->isSuperadmin()) {
            $rules['role'] = ['required', Rule::in(array_keys(User::ROLES))];
            $rules['drop_point_id'] = 'nullable|exists:drop_points,id';
        }

        $data = $request->validate($rules);

        if (empty($data['password'])) {
            unset($data['password']); // tidak mengganti password lama
        }

        if (! $actor->isSuperadmin()) {
            // Petugas selalu membuat/mengubah akun penyetor; input role/drop point diabaikan
            $data['role'] = User::ROLE_USER;
            $data['drop_point_id'] = null;
        } elseif ($data['role'] !== User::ROLE_ADMIN) {
            // Drop point tugas hanya untuk petugas
            $data['drop_point_id'] = null;
        }

        return $data;
    }

    private function dropPointOptions()
    {
        return DropPoint::orderBy('name')->pluck('name', 'id');
    }
}
