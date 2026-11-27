@extends('layouts.admin')

@section('title', 'Detail Setoran')

@section('content')
    <x-page-header :title="$deposit->deposit_number" :description="'Setoran ' . $deposit->user->name">
        <x-button variant="secondary" :href="route('admin.deposits.index')">Kembali</x-button>
        @if ($deposit->isEditable())
            <x-button :href="route('admin.deposits.edit', $deposit)">Ubah</x-button>
        @endif
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card title="Informasi Setoran">
                <dl class="grid gap-4 text-sm sm:grid-cols-2">
                    <div><dt class="text-gray-500">Penyetor</dt><dd class="font-medium text-gray-900">{{ $deposit->user->name }}</dd></div>
                    <div><dt class="text-gray-500">Tanggal</dt><dd class="font-medium text-gray-900">{{ $deposit->deposit_date->format('d/m/Y') }}</dd></div>
                    <div><dt class="text-gray-500">Drop point</dt><dd class="font-medium text-gray-900">{{ $deposit->dropPoint->name }}</dd></div>
                    <div><dt class="text-gray-500">Diterima oleh</dt><dd class="font-medium text-gray-900">{{ $deposit->receiver?->name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Status</dt><dd class="mt-1"><x-status-badge :status="$deposit->status" /></dd></div>
                    <div><dt class="text-gray-500">Pengangkutan</dt>
                        <dd class="font-medium text-gray-900">
                            {{ $deposit->pickup ? $deposit->pickup->pickup_number . ' → ' . ($deposit->pickup->partner?->name ?? '—') : '—' }}
                        </dd>
                    </div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Catatan</dt><dd class="text-gray-900">{{ $deposit->notes ?: '—' }}</dd></div>
                </dl>
            </x-card>

            <x-card title="Detail Barang" :padding="false">
                @include('partials.deposit-items-table')
            </x-card>
        </div>

        <div class="space-y-6">
            <x-card title="Status">
                <x-status-timeline :deposit="$deposit" />

                @if ($deposit->status === \App\Models\Deposit::STATUS_SHIPPED)
                    <form method="POST" action="{{ route('admin.deposits.status', $deposit) }}" class="mt-5"
                        data-confirm="Tandai setoran {{ $deposit->deposit_number }} sudah diserahkan ke mitra?">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ \App\Models\Deposit::STATUS_COMPLETED }}">
                        <x-button class="w-full">Tandai Selesai</x-button>
                    </form>
                @elseif ($deposit->status === \App\Models\Deposit::STATUS_COLLECTED)
                    <p class="mt-5 rounded-lg bg-blue-50 px-3 py-2 text-xs text-blue-700">
                        Status berubah menjadi Diangkut saat setoran dimasukkan ke pengangkutan.
                    </p>
                @endif
            </x-card>

            @if ($deposit->isEditable())
                <form method="POST" action="{{ route('admin.deposits.destroy', $deposit) }}"
                    data-confirm="Hapus setoran {{ $deposit->deposit_number }}? Detail barang ikut terhapus.">
                    @csrf
                    @method('DELETE')
                    <x-button variant="danger" class="w-full">Hapus Setoran</x-button>
                </form>
            @endif
        </div>
    </div>
@endsection
