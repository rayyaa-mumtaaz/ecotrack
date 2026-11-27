@extends('layouts.admin')

@section('title', 'Detail Pengangkutan')

@section('content')
    @php
        $shippedCount = $pickup->deposits->where('status', \App\Models\Deposit::STATUS_SHIPPED)->count();
        $completedCount = $pickup->deposits->where('status', \App\Models\Deposit::STATUS_COMPLETED)->count();
    @endphp

    <x-page-header :title="$pickup->pickup_number" :description="'Diangkut ' . $pickup->pickup_date->format('d/m/Y') . ' ke ' . ($pickup->partner?->name ?? '—')">
        <x-button variant="secondary" :href="route('admin.pickups.index')">Kembali</x-button>
        @if ($canManage)
            <x-button :href="route('admin.pickups.edit', $pickup)">Ubah</x-button>
        @endif
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card title="Jumlah Setoran" :value="$pickup->deposits->count()" icon="deposit" />
        <x-stat-card title="Total Berat" :value="number_format($pickup->total_weight, 2, ',', '.') . ' kg'" icon="box" />
        <x-stat-card title="Sudah Diserahkan" :value="$completedCount . ' / ' . $pickup->deposits->count()" icon="check" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-card title="Setoran yang Diangkut" :padding="false" class="lg:col-span-2">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Nomor</th>
                            <th class="px-5 py-3">Penyetor</th>
                            <th class="px-5 py-3">Drop Point</th>
                            <th class="px-5 py-3 text-right">Berat</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($pickup->deposits as $deposit)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-3">
                                    <a href="{{ route('admin.deposits.show', $deposit) }}" class="font-medium text-emerald-700 hover:underline">{{ $deposit->deposit_number }}</a>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $deposit->user->name }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $deposit->dropPoint->name }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right text-gray-600">{{ number_format($deposit->total_weight, 2, ',', '.') }} kg</td>
                                <td class="whitespace-nowrap px-5 py-3"><x-status-badge :status="$deposit->status" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>

        <div class="space-y-6">
            <x-card title="Mitra Tujuan">
                @if ($pickup->partner)
                    <p class="font-semibold text-gray-900">{{ $pickup->partner->name }}</p>
                    <p class="text-sm text-emerald-700">{{ $pickup->partner->typeLabel() }}</p>
                    <p class="mt-2 text-sm text-gray-600">{{ $pickup->partner->address }}</p>
                    <p class="text-sm text-gray-600">{{ $pickup->partner->contact_person }} {{ $pickup->partner->phone }}</p>
                    <a href="{{ route('admin.partners.show', $pickup->partner) }}" class="mt-2 inline-block text-sm text-emerald-700 hover:underline">Lihat mitra</a>
                @else
                    <p class="text-sm text-gray-500">—</p>
                @endif
                <p class="mt-4 text-sm text-gray-500">Catatan: {{ $pickup->notes ?: '—' }}</p>
            </x-card>

            <x-card title="Aksi">
                @if (! $canManage)
                    <p class="rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-600">Pengangkutan ini memuat setoran dari drop point lain. Perubahan hanya dapat dilakukan superadmin.</p>
                @elseif ($shippedCount > 0)
                    <form method="POST" action="{{ route('admin.pickups.complete', $pickup) }}"
                        data-confirm="Tandai {{ $shippedCount }} setoran sudah diserahkan ke {{ $pickup->partner?->name }}?">
                        @csrf
                        @method('PATCH')
                        <x-button class="w-full">Tandai Diserahkan ke Mitra</x-button>
                    </form>
                    <p class="mt-2 text-xs text-gray-500">Status setoran berubah dari Diangkut menjadi Selesai.</p>
                @else
                    <p class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-700">Semua setoran sudah diserahkan ke mitra.</p>
                @endif

                @if ($canManage && $completedCount === 0)
                    <form method="POST" action="{{ route('admin.pickups.destroy', $pickup) }}" class="mt-4"
                        data-confirm="Batalkan pengangkutan {{ $pickup->pickup_number }}? Setoran kembali berstatus Terkumpul.">
                        @csrf
                        @method('DELETE')
                        <x-button variant="danger" class="w-full">Batalkan Pengangkutan</x-button>
                    </form>
                @endif
            </x-card>
        </div>
    </div>
@endsection
