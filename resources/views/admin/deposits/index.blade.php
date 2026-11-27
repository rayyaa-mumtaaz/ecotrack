@extends('layouts.admin')

@section('title', 'Setoran')

@section('content')
    <x-page-header title="Setoran E-Waste" description="Seluruh transaksi setoran dari semua drop point.">
        <x-button :href="route('admin.deposits.create')">+ Catat Setoran</x-button>
    </x-page-header>

    <x-card :padding="false">
        @php $filterClass = 'rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200'; @endphp
        <x-search-bar :action="route('admin.deposits.index')" placeholder="Cari nomor / nama penyetor...">
            <select name="status" class="{{ $filterClass }}">
                <option value="">Semua status</option>
                @foreach (\App\Models\Deposit::STATUSES as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="drop_point_id" class="{{ $filterClass }}">
                <option value="">Semua drop point</option>
                @foreach ($dropPoints as $id => $name)
                    <option value="{{ $id }}" @selected((string) request('drop_point_id') === (string) $id)>{{ $name }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="{{ $filterClass }}" title="Dari tanggal">
            <input type="date" name="to" value="{{ request('to') }}" class="{{ $filterClass }}" title="Sampai tanggal">
        </x-search-bar>

        @if ($deposits->isEmpty())
            <x-empty-state title="Setoran tidak ditemukan" message="Ubah filter atau catat setoran baru." />
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
                            <th class="px-5 py-3 text-right">Barang</th>
                            <th class="px-5 py-3 text-right">Berat</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($deposits as $deposit)
                            <tr class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-5 py-3 font-medium text-gray-900">{{ $deposit->deposit_number }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $deposit->deposit_date->format('d/m/Y') }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $deposit->user->name }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $deposit->dropPoint->name }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $deposit->receiver?->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-right text-gray-600">{{ $deposit->items_count }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right text-gray-600">{{ number_format($deposit->total_weight, 2, ',', '.') }} kg</td>
                                <td class="whitespace-nowrap px-5 py-3"><x-status-badge :status="$deposit->status" /></td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">
                                    <x-table-actions :show="route('admin.deposits.show', $deposit)"
                                        :edit="$deposit->isEditable() ? route('admin.deposits.edit', $deposit) : null"
                                        :destroy="$deposit->isEditable() ? route('admin.deposits.destroy', $deposit) : null"
                                        :name="'setoran ' . $deposit->deposit_number" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-5 py-3">{{ $deposits->links() }}</div>
        @endif
    </x-card>
@endsection
