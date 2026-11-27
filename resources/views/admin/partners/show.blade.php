@extends('layouts.admin')

@section('title', 'Detail Mitra')

@section('content')
    <x-page-header :title="$partner->name" :description="$partner->typeLabel()">
        <x-button variant="secondary" :href="route('admin.partners.index')">Kembali</x-button>
        @if (auth()->user()->isSuperadmin())
            <x-button :href="route('admin.partners.edit', $partner)">Ubah</x-button>
        @endif
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-card title="Informasi Mitra">
            <dl class="space-y-3 text-sm">
                <div><dt class="text-gray-500">Tipe</dt><dd class="font-medium text-gray-900">{{ $partner->typeLabel() }}</dd></div>
                <div><dt class="text-gray-500">Status</dt><dd class="mt-1"><x-active-badge :active="$partner->is_active" /></dd></div>
                <div><dt class="text-gray-500">Kontak</dt><dd class="text-gray-900">{{ $partner->contact_person ?: '—' }}</dd></div>
                <div><dt class="text-gray-500">Telepon</dt><dd class="text-gray-900">{{ $partner->phone ?: '—' }}</dd></div>
                <div><dt class="text-gray-500">Email</dt><dd class="text-gray-900">{{ $partner->email ?: '—' }}</dd></div>
                <div><dt class="text-gray-500">Alamat</dt><dd class="text-gray-900">{{ $partner->address }}</dd></div>
                <div><dt class="text-gray-500">Deskripsi</dt><dd class="text-gray-900">{{ $partner->description ?: '—' }}</dd></div>
            </dl>
        </x-card>

        <div class="space-y-6 lg:col-span-2">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-stat-card title="Total Pengangkutan" :value="number_format($partner->pickups_count)" icon="truck" />
                <x-stat-card title="Total Berat Diterima" :value="number_format($partner->pickups_sum_total_weight ?? 0, 2, ',', '.') . ' kg'" icon="box" />
            </div>

            <x-card title="Riwayat Pengangkutan" :padding="false">
                @if ($pickups->isEmpty())
                    <x-empty-state icon="truck" title="Belum ada pengangkutan ke mitra ini" />
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-5 py-3">Nomor</th>
                                    <th class="px-5 py-3">Tanggal</th>
                                    <th class="px-5 py-3 text-right">Jumlah Setoran</th>
                                    <th class="px-5 py-3 text-right">Total Berat</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($pickups as $pickup)
                                    <tr>
                                        <td class="whitespace-nowrap px-5 py-3 font-medium text-gray-900">{{ $pickup->pickup_number }}</td>
                                        <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $pickup->pickup_date->format('d/m/Y') }}</td>
                                        <td class="px-5 py-3 text-right text-gray-600">{{ $pickup->deposits_count }}</td>
                                        <td class="whitespace-nowrap px-5 py-3 text-right text-gray-600">{{ number_format($pickup->total_weight, 2, ',', '.') }} kg</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        </div>
    </div>
@endsection
