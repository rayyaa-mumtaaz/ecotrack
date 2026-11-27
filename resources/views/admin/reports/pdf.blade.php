{{-- Laporan PDF (dompdf). Memakai CSS inline sederhana karena dompdf tidak memuat Tailwind. --}}
@php
    $kg = fn ($value) => number_format($value, 2, ',', '.');
    $tables = [
        ['Rekap per Kategori', ['Kategori', 'Jumlah Barang', 'Berat (kg)'], $byCategory, ['name', 'quantity', 'weight']],
        ['Rekap per Drop Point', ['Drop Point', 'Jumlah Setoran', 'Berat (kg)'], $byDropPoint, ['name', 'deposits', 'weight']],
        ['Rekap per Periode', ['Bulan', 'Jumlah Setoran', 'Berat (kg)'], $byPeriod, ['name', 'deposits', 'weight']],
        ['Rekap per Petugas Penerima', ['Petugas', 'Jumlah Setoran', 'Berat (kg)'], $byOfficer, ['name', 'deposits', 'weight']],
        ['Pengangkutan per Mitra', ['Mitra', 'Tipe', 'Jumlah Pengangkutan', 'Berat (kg)'], $byPartner, ['name', 'type', 'pickups', 'weight']],
        ['Pengangkutan per Tipe Mitra', ['Tipe Mitra', 'Jumlah Pengangkutan', 'Berat (kg)'], $byPartnerType, ['name', 'pickups', 'weight']],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan EcoTrack</title>
    <style>
        /* Helvetica = font bawaan PDF, tidak perlu ditanam sehingga ukuran file kecil */
        body { font-family: Helvetica, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 18px; color: #047857; margin: 0; }
        h2 { font-size: 13px; margin: 18px 0 6px; color: #065f46; }
        .muted { color: #6b7280; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 5px 7px; text-align: left; }
        th { background: #ecfdf5; }
        td.num, th.num { text-align: right; }
        .summary td { border: none; padding: 3px 0; }
        .header { border-bottom: 2px solid #059669; padding-bottom: 8px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan EcoTrack</h1>
        <div>Pengelolaan Setoran Limbah Elektronik</div>
        <div class="muted">Periode: {{ $filters['label'] }} · {{ $filters['drop_point'] }} · Dibuat: {{ $generatedAt }}</div>
    </div>

    <h2>Ringkasan</h2>
    <table class="summary">
        <tr><td>Total setoran</td><td>: {{ $summary['deposits'] }} setoran dari {{ $summary['depositors'] }} penyetor</td></tr>
        <tr><td>Total berat</td><td>: {{ $kg($summary['weight']) }} kg ({{ $summary['items'] }} unit barang)</td></tr>
        <tr><td>Pengangkutan</td><td>: {{ $summary['pickups'] }} kali, {{ $kg($summary['pickup_weight']) }} kg</td></tr>
        <tr><td>Status setoran</td>
            <td>: @foreach ($summary['statuses'] as $status => $count){{ \App\Models\Deposit::STATUSES[$status] }} {{ $count }}{{ $loop->last ? '' : ', ' }}@endforeach</td>
        </tr>
    </table>

    @foreach ($tables as [$title, $headers, $rows, $keys])
        <h2>{{ $title }}</h2>
        <table>
            <thead>
                <tr>
                    @foreach ($headers as $index => $header)
                        <th class="{{ $index > 0 && $keys[$index] !== 'type' ? 'num' : '' }}">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        @foreach ($keys as $index => $key)
                            <td class="{{ $index > 0 && $key !== 'type' ? 'num' : '' }}">
                                {{ $key === 'weight' ? $kg($row[$key]) : $row[$key] }}
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ count($headers) }}" class="muted">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endforeach
</body>
</html>
