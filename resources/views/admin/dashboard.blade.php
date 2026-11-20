@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <x-page-header title="Ringkasan EcoTrack" description="Data setoran sampah elektronik yang tercatat di sistem." />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card title="Total Setoran" :value="number_format($stats['total_deposits'])" icon="deposit" hint="Seluruh transaksi" />
        <x-stat-card title="Total Berat" :value="number_format($stats['total_weight'], 2, ',', '.') . ' kg'" icon="box" hint="E-waste terkumpul" />
        <x-stat-card title="Penyetor" :value="number_format($stats['total_users'])" icon="users" hint="Akun role user" />
        <x-stat-card title="Drop Point Aktif" :value="number_format($stats['active_drop_points'])" icon="map-pin" hint="Lokasi beroperasi" />
    </div>

    <x-card title="Setoran Terbaru" :padding="false" class="mt-6">
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
                            <th class="px-5 py-3 text-right">Berat</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($latestDeposits as $deposit)
                            <tr class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-5 py-3 font-medium text-gray-900">{{ $deposit->deposit_number }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $deposit->deposit_date->format('d/m/Y') }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $deposit->user->name }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $deposit->dropPoint->name }}</td>
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
