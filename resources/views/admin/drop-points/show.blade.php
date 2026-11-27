@extends('layouts.admin')

@section('title', 'Detail Drop Point')

@push('head')
    @vite('resources/js/map.js')
@endpush

@section('content')
    <x-page-header :title="$dropPoint->name" :description="$dropPoint->address">
        <x-button variant="secondary" :href="route('admin.drop-points.index')">Kembali</x-button>
        <x-button :href="route('admin.drop-points.edit', $dropPoint)">Ubah</x-button>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card title="Total Setoran" :value="number_format($dropPoint->deposits_count)" icon="deposit" />
        <x-stat-card title="Sedang Terkumpul" :value="number_format($summary['collected_count']) . ' setoran'" icon="box"
            :hint="number_format($summary['collected_weight'], 2, ',', '.') . ' kg belum diangkut'" />
        <x-stat-card title="Total Berat" :value="number_format($summary['total_weight'], 2, ',', '.') . ' kg'" icon="chart" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-card title="Lokasi" :padding="false" class="lg:col-span-2">
            @if ($dropPoint->hasCoordinates())
                <div id="drop-point-picker" class="isolate h-80 w-full rounded-b-xl" data-readonly="1"
                    data-lat="{{ $dropPoint->latitude }}" data-lng="{{ $dropPoint->longitude }}"
                    data-center-lat="{{ $dropPoint->latitude }}" data-center-lng="{{ $dropPoint->longitude }}"></div>
            @else
                <x-empty-state icon="map-pin" title="Lokasi belum diatur" message="Ubah drop point lalu klik peta untuk menentukan titiknya." />
            @endif
        </x-card>

        <div class="space-y-6">
            <x-card title="Informasi">
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500">Status</dt>
                        <dd class="mt-1"><x-active-badge :active="$dropPoint->is_active" /></dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Koordinat</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $dropPoint->hasCoordinates() ? $dropPoint->latitude . ', ' . $dropPoint->longitude : '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Deskripsi</dt>
                        <dd class="text-gray-900">{{ $dropPoint->description ?: '—' }}</dd>
                    </div>
                </dl>
            </x-card>

            <x-card title="Petugas Bertugas">
                @forelse ($dropPoint->officers as $officer)
                    <div class="flex items-center gap-3 py-1.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-sm font-semibold text-emerald-700">
                            {{ strtoupper(substr($officer->name, 0, 1)) }}
                        </span>
                        <div class="text-sm">
                            <p class="font-medium text-gray-900">{{ $officer->name }}</p>
                            <p class="text-gray-500">{{ $officer->email }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Belum ada petugas. Penugasan diatur di menu Pengguna (Modul 1).</p>
                @endforelse
            </x-card>
        </div>
    </div>

    <x-card title="Setoran Terbaru di Drop Point Ini" :padding="false" class="mt-6">
        @if ($latestDeposits->isEmpty())
            <x-empty-state title="Belum ada setoran" />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Nomor</th>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Penyetor</th>
                            <th class="px-5 py-3">Diterima oleh</th>
                            <th class="px-5 py-3 text-right">Berat</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($latestDeposits as $deposit)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-3 font-medium text-gray-900">{{ $deposit->deposit_number }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $deposit->deposit_date->format('d/m/Y') }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $deposit->user->name }}</td>
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

    <div class="mt-6">
        <form method="POST" action="{{ route('admin.drop-points.destroy', $dropPoint) }}"
            data-confirm="Hapus drop point {{ $dropPoint->name }}?">
            @csrf
            @method('DELETE')
            <x-button variant="danger">Hapus Drop Point</x-button>
        </form>
    </div>
@endsection
