@extends('layouts.user')

@section('title', 'Riwayat Setoran')

@section('content')
    <x-page-header title="Riwayat Setoran" description="Daftar setoran sampah elektronik milik Anda." />

    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <x-stat-card title="Total Setoran" :value="number_format($summary['count'])" icon="deposit" />
        <x-stat-card title="Total Berat Disetor" :value="number_format($summary['weight'], 2, ',', '.') . ' kg'" icon="box" />
    </div>

    <x-card :padding="false">
        <form method="GET" action="{{ route('my-deposits.index') }}" class="flex flex-wrap items-center gap-2 border-b border-gray-200 p-4">
            <label for="status" class="text-sm text-gray-600">Status:</label>
            <select id="status" name="status" onchange="this.form.submit()"
                class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                <option value="">Semua</option>
                @foreach (\App\Models\Deposit::STATUSES as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </form>

        @if ($deposits->isEmpty())
            <x-empty-state title="Belum ada setoran"
                message="Bawa barang elektronik bekas ke drop point terdekat. Setoran yang dicatat petugas akan tampil di sini." />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Nomor</th>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3">Drop Point</th>
                            <th class="px-5 py-3">Diterima oleh</th>
                            <th class="px-5 py-3 text-right">Berat</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($deposits as $deposit)
                            <tr class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-5 py-3 font-medium text-gray-900">{{ $deposit->deposit_number }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $deposit->deposit_date->format('d/m/Y') }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $deposit->dropPoint->name }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $deposit->receiver?->name ?? '—' }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right text-gray-600">{{ number_format($deposit->total_weight, 2, ',', '.') }} kg</td>
                                <td class="whitespace-nowrap px-5 py-3"><x-status-badge :status="$deposit->status" /></td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">
                                    <a href="{{ route('my-deposits.show', $deposit) }}" class="text-emerald-700 hover:underline">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-5 py-3">{{ $deposits->links() }}</div>
        @endif
    </x-card>

    <x-card title="Keterangan Status" class="mt-6">
        <ul class="space-y-3 text-sm text-gray-600">
            <li class="flex items-center gap-3"><x-status-badge status="collected" /> Barang diterima dan dicatat di drop point.</li>
            <li class="flex items-center gap-3"><x-status-badge status="shipped" /> Barang sedang diangkut dari drop point.</li>
            <li class="flex items-center gap-3"><x-status-badge status="completed" /> Barang telah diserahkan ke mitra pengelola.</li>
        </ul>
    </x-card>
@endsection
