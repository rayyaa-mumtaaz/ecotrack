@extends('layouts.admin')

@section('title', 'Dashboard')

@push('head')
    @vite('resources/js/map.js')
@endpush

@section('content')
    <x-page-header title="Ringkasan EcoTrack" description="Data setoran sampah elektronik yang tercatat di sistem.">
        <x-button :href="route('admin.deposits.create')">+ Catat Setoran</x-button>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card title="Total Setoran" :value="number_format($stats['total_deposits'])" icon="deposit" hint="Seluruh transaksi" />
        <x-stat-card title="Total Berat" :value="number_format($stats['total_weight'], 2, ',', '.') . ' kg'" icon="box" hint="E-waste terkumpul" />
        <x-stat-card title="Penyetor" :value="number_format($stats['total_users'])" icon="users" hint="Akun role user" />
        <x-stat-card title="Drop Point Aktif" :value="number_format($stats['active_drop_points'])" icon="map-pin" hint="Lokasi beroperasi" />
    </div>

    @if ($stats['waiting_deposits'] > 0)
        <div class="mt-6 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-blue-200 bg-blue-50 px-5 py-4">
            <p class="text-sm text-blue-800">
                <strong>{{ $stats['waiting_deposits'] }} setoran</strong>
                ({{ number_format($stats['waiting_weight'], 2, ',', '.') }} kg) masih Terkumpul dan menunggu diangkut ke mitra.
            </p>
            <x-button :href="route('admin.pickups.create')">Catat Pengangkutan</x-button>
        </div>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-card title="Ringkasan per Kategori">
            @php $maxWeight = max($categorySummary->max('weight') ?? 0, 0.01); @endphp
            @forelse ($categorySummary as $row)
                <div class="mb-4 last:mb-0">
                    <div class="flex justify-between text-sm">
                        <span class="font-medium text-gray-800">{{ $row->category->name }}</span>
                        <span class="text-gray-500">{{ $row->quantity }} unit · {{ number_format($row->weight, 2, ',', '.') }} kg</span>
                    </div>
                    <div class="mt-1 h-2 rounded-full bg-gray-100">
                        <div class="h-2 rounded-full bg-emerald-500" style="width: {{ round($row->weight / $maxWeight * 100) }}%"></div>
                    </div>
                </div>
            @empty
                <x-empty-state icon="tag" title="Belum ada barang tercatat" />
            @endforelse
        </x-card>

        <x-card title="Sebaran Drop Point" :padding="false">
            <x-slot:actions>
                <a href="{{ route('admin.drop-points.index') }}" class="text-sm text-emerald-700 hover:underline">Kelola</a>
            </x-slot:actions>
            <div id="drop-point-map" class="isolate h-72 w-full rounded-b-xl"
                data-url="{{ route('drop-points.map-data') }}"
                data-center-lat="{{ \App\Models\DropPoint::MAP_CENTER[0] }}"
                data-center-lng="{{ \App\Models\DropPoint::MAP_CENTER[1] }}"></div>
        </x-card>
    </div>

    <x-card title="Setoran Terbaru" :padding="false" class="mt-6">
        <x-slot:actions>
            <a href="{{ route('admin.deposits.index') }}" class="text-sm text-emerald-700 hover:underline">Lihat semua</a>
        </x-slot:actions>

        @if ($latestDeposits->isEmpty())
            <x-empty-state title="Belum ada setoran" message="Setoran yang dicatat akan tampil di sini." />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Nomor</th>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Penyetor</th>
                            <th class="px-5 py-3">Drop Point</th>
                            <th class="px-5 py-3">Diterima oleh</th>
                            <th class="px-5 py-3 text-right">Berat</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($latestDeposits as $deposit)
                            <tr class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-5 py-3">
                                    <a href="{{ route('admin.deposits.show', $deposit) }}" class="font-medium text-gray-900 hover:text-emerald-700">{{ $deposit->deposit_number }}</a>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $deposit->deposit_date->format('d/m/Y') }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $deposit->user->name }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $deposit->dropPoint->name }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $deposit->receiver?->name ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right text-gray-600">{{ number_format($deposit->total_weight, 2, ',', '.') }} kg</td>
                                <td class="whitespace-nowrap px-5 py-3"><x-status-badge :status="$deposit->status" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
@endsection
