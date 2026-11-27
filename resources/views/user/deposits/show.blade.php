@extends('layouts.user')

@section('title', 'Detail Setoran')

@section('content')
    <x-page-header :title="$deposit->deposit_number" :description="'Disetor ' . $deposit->deposit_date->format('d/m/Y') . ' di ' . $deposit->dropPoint->name">
        <x-button variant="secondary" :href="route('my-deposits.index')">Kembali</x-button>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card title="Informasi Setoran">
                <dl class="grid gap-4 text-sm sm:grid-cols-2">
                    <div><dt class="text-gray-500">Drop point</dt><dd class="font-medium text-gray-900">{{ $deposit->dropPoint->name }}</dd></div>
                    <div><dt class="text-gray-500">Diterima oleh</dt><dd class="font-medium text-gray-900">{{ $deposit->receiver?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Status</dt><dd class="mt-1"><x-status-badge :status="$deposit->status" /></dd></div>
                    <div><dt class="text-gray-500">Total berat</dt><dd class="font-medium text-gray-900">{{ number_format($deposit->total_weight, 2, ',', '.') }} kg</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Catatan</dt><dd class="text-gray-900">{{ $deposit->notes ?: '—' }}</dd></div>
                </dl>
            </x-card>

            <x-card title="Detail Barang" :padding="false">
                @include('partials.deposit-items-table')
            </x-card>
        </div>

        <x-card title="Lacak Status">
            <x-status-timeline :deposit="$deposit" />
        </x-card>
    </div>
@endsection
