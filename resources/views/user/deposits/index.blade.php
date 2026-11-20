@extends('layouts.user')

@section('title', 'Riwayat Setoran')

@section('content')
    <x-page-header title="Riwayat Setoran" description="Daftar setoran sampah elektronik milik Anda." />

    {{-- Daftar setoran milik user yang login dibuat pada Stage 3 (login) dan Stage 5 (Modul 4) --}}
    <x-card :padding="false">
        <x-empty-state title="Belum ada data untuk ditampilkan"
            message="Riwayat setoran akan tampil setelah Anda login. Fitur ini sedang dikembangkan." />
    </x-card>

    <x-card title="Keterangan Status" class="mt-6">
        <ul class="space-y-3 text-sm text-gray-600">
            <li class="flex items-center gap-3"><x-status-badge status="collected" /> Barang diterima dan dicatat di drop point.</li>
            <li class="flex items-center gap-3"><x-status-badge status="shipped" /> Barang sedang diangkut dari drop point.</li>
            <li class="flex items-center gap-3"><x-status-badge status="completed" /> Barang telah diserahkan ke mitra pengelola.</li>
        </ul>
    </x-card>
@endsection
