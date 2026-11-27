{{-- Tabel rekap sederhana. Variabel: $rows, $headers, $keys. Kolom 'weight' diformat kg, kolom angka rata kanan. --}}
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
            <tr>
                @foreach ($headers as $index => $header)
                    <th class="px-5 py-3 {{ $index > 0 && ! in_array($keys[$index], ['type']) ? 'text-right' : '' }}">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($rows as $row)
                <tr>
                    @foreach ($keys as $index => $key)
                        @if ($key === 'weight')
                            <td class="whitespace-nowrap px-5 py-3 text-right text-gray-600">{{ number_format($row[$key], 2, ',', '.') }} kg</td>
                        @elseif ($index === 0)
                            <td class="px-5 py-3 text-gray-900">{{ $row[$key] }}</td>
                        @else
                            <td class="px-5 py-3 {{ $key === 'type' ? '' : 'text-right' }} text-gray-600">{{ $row[$key] }}</td>
                        @endif
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($headers) }}" class="px-5 py-6 text-center text-gray-500">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
