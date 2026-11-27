{{-- Tabel detail barang setoran. Dipakai halaman detail admin dan user. Variabel: $deposit --}}
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
            <tr>
                <th class="px-5 py-3">Barang</th>
                <th class="px-5 py-3">Kategori</th>
                <th class="px-5 py-3 text-right">Jumlah</th>
                <th class="px-5 py-3 text-right">Berat</th>
                <th class="px-5 py-3">Kondisi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach ($deposit->items as $item)
                <tr>
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-900">{{ $item->item_name }}</p>
                        @if ($item->notes)
                            <p class="text-xs text-gray-500">{{ $item->notes }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-gray-600">{{ $item->category->name }}</td>
                    <td class="px-5 py-3 text-right text-gray-600">{{ $item->quantity }}</td>
                    <td class="whitespace-nowrap px-5 py-3 text-right text-gray-600">{{ number_format($item->weight, 2, ',', '.') }} kg</td>
                    <td class="whitespace-nowrap px-5 py-3 text-gray-600">{{ $item->conditionLabel() }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-gray-50 font-semibold text-gray-900">
            <tr>
                <td class="px-5 py-3" colspan="3">Total berat</td>
                <td class="whitespace-nowrap px-5 py-3 text-right">{{ number_format($deposit->total_weight, 2, ',', '.') }} kg</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
