@extends('layouts.admin')

@section('title', 'Drop Point')

@push('head')
    @vite('resources/js/map.js')
@endpush

@section('content')
    <x-page-header title="Drop Point" description="Lokasi pengumpulan e-waste beserta petugas yang bertugas.">
        @if (auth()->user()->isSuperadmin())
            <x-button :href="route('admin.drop-points.create')">+ Tambah Drop Point</x-button>
        @endif
    </x-page-header>

    {{-- Peta sebaran: data diambil dari /drop-points/map-data dan diperbarui tiap 30 detik --}}
    <x-card title="Peta Sebaran" :padding="false">
        <x-slot:actions>
            <span id="map-updated-at" class="text-xs text-gray-400">Memuat peta...</span>
        </x-slot:actions>
        <div id="drop-point-map" class="isolate h-80 w-full rounded-b-xl"
            data-url="{{ route('drop-points.map-data') }}"
            data-center-lat="{{ \App\Models\DropPoint::MAP_CENTER[0] }}"
            data-center-lng="{{ \App\Models\DropPoint::MAP_CENTER[1] }}"></div>
        <p id="map-error" class="hidden px-5 py-3 text-sm text-red-600">Data peta gagal dimuat. Mencoba lagi dalam 30 detik.</p>
    </x-card>

    <x-card :padding="false" class="mt-6">
        <form method="GET" action="{{ route('admin.drop-points.index') }}" class="flex flex-col gap-2 border-b border-gray-200 p-4 sm:flex-row">
            <input type="search" name="search" value="{{ $search }}" placeholder="Cari nama atau alamat..."
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 sm:max-w-xs">
            <x-button variant="secondary">Cari</x-button>
        </form>

        @if ($dropPoints->isEmpty())
            <x-empty-state icon="map-pin" title="Drop point tidak ditemukan"
                :message="$search ? 'Coba kata kunci lain.' : 'Tambahkan drop point pertama.'" />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">Alamat</th>
                            <th class="px-5 py-3">Petugas</th>
                            <th class="px-5 py-3">Lokasi</th>
                            <th class="px-5 py-3 text-right">Setoran</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($dropPoints as $dropPoint)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $dropPoint->name }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $dropPoint->address }}</td>
                                <td class="px-5 py-3 text-gray-600">
                                    {{ $dropPoint->officers->pluck('name')->join(', ') ?: '—' }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">
                                    @if ($dropPoint->hasCoordinates())
                                        <span class="text-emerald-700">Sudah diatur</span>
                                    @else
                                        <span class="text-amber-600">Belum diatur</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right text-gray-600">{{ $dropPoint->deposits_count }}</td>
                                <td class="px-5 py-3"><x-active-badge :active="$dropPoint->is_active" /></td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">
                                    <div class="inline-flex items-center gap-3">
                                        <a href="{{ route('admin.drop-points.show', $dropPoint) }}" class="text-emerald-700 hover:underline">Detail</a>
                                        @if (auth()->user()->isSuperadmin())
                                            <a href="{{ route('admin.drop-points.edit', $dropPoint) }}" class="text-emerald-700 hover:underline">Ubah</a>
                                            <form method="POST" action="{{ route('admin.drop-points.destroy', $dropPoint) }}"
                                                data-confirm="Hapus drop point {{ $dropPoint->name }}?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-5 py-3">
                {{ $dropPoints->links() }}
            </div>
        @endif
    </x-card>
@endsection
