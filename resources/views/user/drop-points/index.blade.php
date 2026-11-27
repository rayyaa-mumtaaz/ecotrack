@extends('layouts.user')

@section('title', 'Drop Point')

@push('head')
    @vite('resources/js/map.js')
@endpush

@section('content')
    <x-page-header title="Peta Drop Point" description="Temukan lokasi pengumpulan e-waste terdekat dari posisi Anda.">
        <x-button type="button" id="locate-me">
            <x-icon name="map-pin" class="h-4 w-4" /> Lokasi saya
        </x-button>
    </x-page-header>

    <p id="locate-status" class="-mt-3 mb-4 text-sm text-gray-500">
        Tekan "Lokasi saya" untuk melihat drop point terdekat.
    </p>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-card :padding="false" class="lg:col-span-2">
            <div id="drop-point-map" class="isolate h-[420px] w-full rounded-t-xl lg:h-[560px]"
                data-url="{{ route('drop-points.map-data') }}"
                data-center-lat="{{ \App\Models\DropPoint::MAP_CENTER[0] }}"
                data-center-lng="{{ \App\Models\DropPoint::MAP_CENTER[1] }}"></div>
            <div class="flex items-center justify-between border-t border-gray-200 px-4 py-2 text-xs text-gray-400">
                <span>Data diperbarui otomatis tiap 30 detik</span>
                <span id="map-updated-at">Memuat...</span>
            </div>
            <p id="map-error" class="hidden px-4 pb-3 text-sm text-red-600">Data peta gagal dimuat. Mencoba lagi dalam 30 detik.</p>
            <noscript><p class="p-4 text-sm text-red-600">Aktifkan JavaScript untuk melihat peta.</p></noscript>
        </x-card>

        <div>
            <h2 class="mb-3 font-semibold text-gray-900">Daftar Drop Point</h2>
            <div id="drop-point-list" class="space-y-3 lg:max-h-[560px] lg:overflow-y-auto lg:pr-1">
                <p class="py-6 text-center text-sm text-gray-500">Memuat data...</p>
            </div>
        </div>
    </div>
@endsection
