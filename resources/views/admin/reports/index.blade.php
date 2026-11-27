@extends('layouts.admin')

@section('title', 'Laporan')

@php
    $kg = fn ($value) => number_format($value, 2, ',', '.') . ' kg';
    $query = array_filter(request()->only(['start_date', 'end_date']));
@endphp

@section('content')
    <x-page-header title="Laporan" :description="'Periode: ' . $filters['label']">
        <x-button variant="secondary" :href="route('admin.reports.export.pdf', $query)">Ekspor PDF</x-button>
        <x-button :href="route('admin.reports.export.excel', $query)">Ekspor Excel</x-button>
    </x-page-header>

    <x-card class="mb-6">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="start_date" class="mb-1 block text-sm font-medium text-gray-700">Dari tanggal</label>
                <input type="date" id="start_date" name="start_date" value="{{ $filters['start_date'] }}"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
            </div>
            <div>
                <label for="end_date" class="mb-1 block text-sm font-medium text-gray-700">Sampai tanggal</label>
                <input type="date" id="end_date" name="end_date" value="{{ $filters['end_date'] }}"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
            </div>
            <x-button variant="secondary">Tampilkan</x-button>
            @if ($query)
                <a href="{{ route('admin.reports.index') }}" class="py-2 text-sm text-gray-500 hover:underline">Reset</a>
            @endif
        </form>
        @error('end_date')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
        <p class="mt-3 text-xs text-gray-400">File ekspor dibuat saat diunduh dan langsung dikirim ke browser, tidak disimpan di server.</p>
    </x-card>

    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        <x-stat-card title="Total Setoran" :value="number_format($summary['deposits'])" icon="deposit"
            :hint="$summary['depositors'] . ' penyetor'" />
        <x-stat-card title="Total Berat" :value="$kg($summary['weight'])" icon="box" :hint="number_format($summary['items']) . ' unit barang'" />
        <x-stat-card title="Pengangkutan" :value="number_format($summary['pickups'])" icon="truck" :hint="$kg($summary['pickup_weight']) . ' diangkut'" />
        <x-stat-card title="Status Setoran" icon="check"
            :value="$summary['statuses']['completed'] . ' selesai'"
            :hint="$summary['statuses']['collected'] . ' terkumpul · ' . $summary['statuses']['shipped'] . ' diangkut'" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Rekap per kategori dengan batang persentase berat --}}
        <x-card title="Rekap per Kategori" :padding="false">
            @php $maxWeight = max($byCategory->max('weight') ?? 0, 0.01); @endphp
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                    <tr><th class="px-5 py-3">Kategori</th><th class="px-5 py-3 text-right">Barang</th><th class="px-5 py-3 text-right">Berat</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($byCategory as $row)
                        <tr>
                            <td class="px-5 py-3">
                                <p class="text-gray-900">{{ $row['name'] }}</p>
                                <div class="mt-1 h-1.5 rounded-full bg-gray-100">
                                    <div class="h-1.5 rounded-full bg-emerald-500" style="width: {{ round($row['weight'] / $maxWeight * 100) }}%"></div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-right text-gray-600">{{ $row['quantity'] }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-gray-600">{{ $kg($row['weight']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-6 text-center text-gray-500">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>

        <x-card title="Rekap per Periode (Bulan)" :padding="false">
            @include('admin.reports._table', ['rows' => $byPeriod, 'headers' => ['Bulan', 'Setoran', 'Berat'], 'keys' => ['name', 'deposits', 'weight']])
        </x-card>

        <x-card title="Rekap per Drop Point" :padding="false">
            @include('admin.reports._table', ['rows' => $byDropPoint, 'headers' => ['Drop Point', 'Setoran', 'Berat'], 'keys' => ['name', 'deposits', 'weight']])
        </x-card>

        <x-card title="Rekap per Petugas Penerima" :padding="false">
            @include('admin.reports._table', ['rows' => $byOfficer, 'headers' => ['Petugas', 'Setoran', 'Berat'], 'keys' => ['name', 'deposits', 'weight']])
        </x-card>

        <x-card title="Pengangkutan per Mitra" :padding="false">
            @include('admin.reports._table', ['rows' => $byPartner, 'headers' => ['Mitra', 'Tipe', 'Pengangkutan', 'Berat'], 'keys' => ['name', 'type', 'pickups', 'weight']])
        </x-card>

        <x-card title="Pengangkutan per Tipe Mitra" :padding="false">
            @include('admin.reports._table', ['rows' => $byPartnerType, 'headers' => ['Tipe Mitra', 'Pengangkutan', 'Berat'], 'keys' => ['name', 'pickups', 'weight']])
        </x-card>
    </div>
@endsection
