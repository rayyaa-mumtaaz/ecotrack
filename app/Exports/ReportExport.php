<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Ekspor laporan EcoTrack ke Excel (.xlsx) dengan beberapa sheet.
 * Data berasal dari ReportController::buildReport(), sama dengan halaman laporan.
 */
class ReportExport implements Export, WithMultipleSheets
{
    public function __construct(private array $report)
    {
    }

    public function sheets(): array
    {
        $summary = $this->report['summary'];

        $summaryRows = [
            ['Periode', $this->report['filters']['label']],
            ['Drop point', $this->report['filters']['drop_point']],
            ['Dibuat', $this->report['generatedAt']],
            ['Total setoran', $summary['deposits']],
            ['Total berat (kg)', round($summary['weight'], 2)],
            ['Total barang (unit)', $summary['items']],
            ['Jumlah penyetor', $summary['depositors']],
            ['Total pengangkutan', $summary['pickups']],
            ['Berat diangkut (kg)', round($summary['pickup_weight'], 2)],
        ];

        foreach ($summary['statuses'] as $status => $count) {
            $summaryRows[] = ['Setoran ' . \App\Models\Deposit::STATUSES[$status], $count];
        }

        return [
            new ReportSheet('Ringkasan', ['Keterangan', 'Nilai'], $summaryRows),
            new ReportSheet('Per Kategori', ['Kategori', 'Jumlah Barang', 'Berat (kg)'],
                $this->rows($this->report['byCategory'], ['name', 'quantity', 'weight'])),
            new ReportSheet('Per Drop Point', ['Drop Point', 'Jumlah Setoran', 'Berat (kg)'],
                $this->rows($this->report['byDropPoint'], ['name', 'deposits', 'weight'])),
            new ReportSheet('Per Periode', ['Bulan', 'Jumlah Setoran', 'Berat (kg)'],
                $this->rows($this->report['byPeriod'], ['name', 'deposits', 'weight'])),
            new ReportSheet('Per Petugas', ['Petugas Penerima', 'Jumlah Setoran', 'Berat (kg)'],
                $this->rows($this->report['byOfficer'], ['name', 'deposits', 'weight'])),
            new ReportSheet('Per Mitra', ['Mitra', 'Tipe', 'Jumlah Pengangkutan', 'Berat (kg)'],
                $this->rows($this->report['byPartner'], ['name', 'type', 'pickups', 'weight'])),
            new ReportSheet('Per Tipe Mitra', ['Tipe Mitra', 'Jumlah Pengangkutan', 'Berat (kg)'],
                $this->rows($this->report['byPartnerType'], ['name', 'pickups', 'weight'])),
        ];
    }

    /**
     * Ubah koleksi rekap menjadi baris array sesuai urutan kolom.
     */
    private function rows($collection, array $keys): array
    {
        return collect($collection)->map(function ($row) use ($keys) {
            return array_map(fn ($key) => is_float($row[$key]) ? round($row[$key], 2) : $row[$key], $keys);
        })->all();
    }
}
