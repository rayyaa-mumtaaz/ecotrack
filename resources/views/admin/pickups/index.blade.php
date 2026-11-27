@extends('layouts.admin')

@section('title', 'Pengangkutan')

@section('content')
    <x-page-header title="Pengangkutan" description="Pengiriman setoran dari drop point ke mitra pengelola.">
        <x-button :href="route('admin.pickups.create')">+ Catat Pengangkutan</x-button>
    </x-page-header>

    @if ($waitingCount > 0)
        <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
            Ada <strong>{{ $waitingCount }}</strong> setoran berstatus Terkumpul yang menunggu diangkut.
        </div>
    @endif

    <x-card :padding="false">
        <x-search-bar :action="route('admin.pickups.index')" placeholder="Cari nomor pengangkutan...">
            <select name="partner_id" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                <option value="">Semua mitra</option>
                @foreach ($partners as $id => $name)
                    <option value="{{ $id }}" @selected((string) request('partner_id') === (string) $id)>{{ $name }}</option>
                @endforeach
            </select>
        </x-search-bar>

        @if ($pickups->isEmpty())
            <x-empty-state icon="truck" title="Belum ada pengangkutan" />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Nomor</th>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Mitra</th>
                            <th class="px-5 py-3 text-right">Setoran</th>
                            <th class="px-5 py-3 text-right">Total Berat</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($pickups as $pickup)
                            @php $completed = $pickup->deposits_count > 0 && $pickup->completed_count === $pickup->deposits_count; @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-5 py-3 font-medium text-gray-900">{{ $pickup->pickup_number }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $pickup->pickup_date->format('d/m/Y') }}</td>
                                <td class="px-5 py-3 text-gray-600">
                                    {{ $pickup->partner?->name ?? '—' }}
                                    @if ($pickup->partner)
                                        <span class="block text-xs text-gray-400">{{ $pickup->partner->typeLabel() }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right text-gray-600">{{ $pickup->deposits_count }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right text-gray-600">{{ number_format($pickup->total_weight, 2, ',', '.') }} kg</td>
                                <td class="whitespace-nowrap px-5 py-3">
                                    <x-status-badge :status="$completed ? 'completed' : 'shipped'" />
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">
                                    <x-table-actions :show="route('admin.pickups.show', $pickup)" :edit="route('admin.pickups.edit', $pickup)" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-5 py-3">{{ $pickups->links() }}</div>
        @endif
    </x-card>
@endsection
