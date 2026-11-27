@extends('layouts.admin')

@section('title', 'Catat Pengangkutan')

@section('content')
    <x-page-header title="Catat Pengangkutan" description="Pilih mitra tujuan dan setoran yang diangkut. Status setoran berubah menjadi Diangkut." />

    <form method="POST" action="{{ route('admin.pickups.store') }}">
        @csrf

        <x-card title="Data Pengangkutan">
            @include('admin.pickups._fields')
        </x-card>

        <x-card :padding="false" class="mt-6">
            <x-slot:title>Setoran Siap Diangkut ({{ $availableDeposits->count() }})</x-slot:title>
            <x-slot:actions>
                <select id="filter-drop-point" class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                    <option value="">Semua drop point</option>
                    @foreach ($dropPoints as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </x-slot:actions>

            @error('deposit_ids')
                <p class="mx-5 mt-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>
            @enderror
            @error('deposit_ids.*')
                <p class="mx-5 mt-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>
            @enderror

            @if ($availableDeposits->isEmpty())
                <x-empty-state title="Tidak ada setoran berstatus Terkumpul" message="Semua setoran sudah diangkut." />
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="w-10 px-5 py-3">
                                    <input type="checkbox" id="select-all" title="Pilih semua"
                                        class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                </th>
                                <th class="px-5 py-3">Nomor</th>
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3">Penyetor</th>
                                <th class="px-5 py-3">Drop Point</th>
                                <th class="px-5 py-3 text-right">Barang</th>
                                <th class="px-5 py-3 text-right">Berat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($availableDeposits as $deposit)
                                <tr class="deposit-row hover:bg-gray-50" data-drop-point="{{ $deposit->drop_point_id }}">
                                    <td class="px-5 py-3">
                                        <input type="checkbox" name="deposit_ids[]" value="{{ $deposit->id }}"
                                            data-weight="{{ $deposit->total_weight }}"
                                            class="deposit-check rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                            @checked(in_array($deposit->id, old('deposit_ids', [])))>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3 font-medium text-gray-900">{{ $deposit->deposit_number }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $deposit->deposit_date->format('d/m/Y') }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $deposit->user->name }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $deposit->dropPoint->name }}</td>
                                    <td class="px-5 py-3 text-right text-gray-600">{{ $deposit->items_count }}</td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right text-gray-600">{{ number_format($deposit->total_weight, 2, ',', '.') }} kg</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 px-5 py-4">
                <p class="text-sm text-gray-600">
                    Terpilih: <strong id="selected-count">0</strong> setoran,
                    total <strong id="selected-weight" class="text-emerald-700">0,00</strong> kg
                </p>
                <div class="flex gap-2">
                    <x-button variant="secondary" :href="route('admin.pickups.index')">Batal</x-button>
                    <x-button>Simpan Pengangkutan</x-button>
                </div>
            </div>
        </x-card>
    </form>
@endsection

@push('scripts')
    <script type="module">
        // jQuery: pilih semua, filter per drop point, hitung jumlah & berat setoran terpilih
        function updateSummary() {
            let count = 0;
            let weight = 0;
            $('.deposit-check:checked').each(function () {
                count++;
                weight += parseFloat($(this).data('weight')) || 0;
            });
            $('#selected-count').text(count);
            $('#selected-weight').text(weight.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

            const $visible = $('.deposit-row:visible .deposit-check');
            $('#select-all').prop('checked', $visible.length > 0 && $visible.filter(':checked').length === $visible.length);
        }

        // "Pilih semua" hanya berlaku untuk baris yang sedang tampil (sesuai filter)
        $('#select-all').on('change', function () {
            $('.deposit-row:visible .deposit-check').prop('checked', this.checked);
            updateSummary();
        });

        $('.deposit-check').on('change', updateSummary);

        $('#filter-drop-point').on('change', function () {
            const id = $(this).val();
            $('.deposit-row').each(function () {
                $(this).toggle(!id || String($(this).data('drop-point')) === id);
            });
            updateSummary();
        });

        updateSummary();
    </script>
@endpush
