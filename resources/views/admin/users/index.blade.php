@extends('layouts.admin')

@section('title', 'Pengguna')

@section('content')
    <x-page-header title="Pengguna" :description="auth()->user()->isSuperadmin() ? 'Akun superadmin, petugas drop point, dan penyetor.' : 'Akun penyetor.'">
        <x-button :href="route('admin.users.create')">+ Tambah Pengguna</x-button>
    </x-page-header>

    <x-card :padding="false">
        <x-search-bar :action="route('admin.users.index')" placeholder="Cari nama / email...">
            @if (auth()->user()->isSuperadmin())
            <select name="role" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                <option value="">Semua role</option>
                @foreach (\App\Models\User::ROLES as $value => $label)
                    <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @endif
        </x-search-bar>

        @if ($users->isEmpty())
            <x-empty-state icon="users" title="Pengguna tidak ditemukan" />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">Role</th>
                            <th class="px-5 py-3">Drop Point Tugas</th>
                            <th class="px-5 py-3 text-right">Setoran</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3">
                                    <p class="font-medium text-gray-900">
                                        {{ $user->name }}
                                        @if ($user->is(auth()->user()))
                                            <span class="text-xs font-normal text-gray-400">(Anda)</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3">
                                    <span @class([
                                        'rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                        'bg-purple-100 text-purple-700' => $user->isSuperadmin(),
                                        'bg-emerald-100 text-emerald-700' => $user->isAdmin(),
                                        'bg-blue-100 text-blue-700' => ! $user->isStaff(),
                                    ])>{{ \App\Models\User::ROLES[$user->role] }}</span>
                                </td>
                                <td class="px-5 py-3 text-gray-600">{{ $user->isAdmin() ? ($user->dropPoint?->name ?? 'Belum ditugaskan') : ($user->isSuperadmin() ? 'Semua drop point' : '—') }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right text-gray-600">
                                    @if ($user->isStaff())
                                        {{ $user->received_deposits_count }} <span class="text-xs text-gray-400">diterima</span>
                                    @else
                                        {{ $user->deposits_count }} <span class="text-xs text-gray-400">disetor</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">
                                    <x-table-actions :edit="route('admin.users.edit', $user)"
                                        :destroy="$user->is(auth()->user()) ? null : route('admin.users.destroy', $user)"
                                        :name="'pengguna ' . $user->name" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-5 py-3">{{ $users->links() }}</div>
        @endif
    </x-card>
@endsection
