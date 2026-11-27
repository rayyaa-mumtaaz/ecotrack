@extends('layouts.admin')

@section('title', 'Mitra')

@section('content')
    <x-page-header title="Mitra Pengelola" description="Pihak penerima e-waste: pendaur ulang, pengrajin, pengepul, servis, dan lainnya.">
        @if (auth()->user()->isSuperadmin())
            <x-button :href="route('admin.partners.create')">+ Tambah Mitra</x-button>
        @endif
    </x-page-header>

    <x-card :padding="false">
        <x-search-bar :action="route('admin.partners.index')" placeholder="Cari nama mitra / kontak...">
            <select name="type" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                <option value="">Semua tipe</option>
                @foreach (\App\Models\Partner::TYPES as $value => $label)
                    <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </x-search-bar>

        @if ($partners->isEmpty())
            <x-empty-state icon="users" title="Mitra tidak ditemukan" />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">Tipe</th>
                            <th class="px-5 py-3">Kontak</th>
                            <th class="px-5 py-3 text-right">Pengangkutan</th>
                            <th class="px-5 py-3 text-right">Berat Diterima</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($partners as $partner)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3">
                                    <p class="font-medium text-gray-900">{{ $partner->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $partner->address }}</p>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3">
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">{{ $partner->typeLabel() }}</span>
                                </td>
                                <td class="px-5 py-3 text-gray-600">
                                    {{ $partner->contact_person ?: '—' }}
                                    @if ($partner->phone)
                                        <span class="block text-xs text-gray-500">{{ $partner->phone }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right text-gray-600">{{ $partner->pickups_count }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right text-gray-600">
                                    {{ number_format($partner->pickups_sum_total_weight ?? 0, 2, ',', '.') }} kg
                                </td>
                                <td class="px-5 py-3"><x-active-badge :active="$partner->is_active" /></td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">
                                    <x-table-actions :show="route('admin.partners.show', $partner)" :edit="auth()->user()->isSuperadmin() ? route('admin.partners.edit', $partner) : null"
                                        :destroy="auth()->user()->isSuperadmin() ? route('admin.partners.destroy', $partner) : null" :name="'mitra ' . $partner->name" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-5 py-3">{{ $partners->links() }}</div>
        @endif
    </x-card>
@endsection
