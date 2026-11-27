@extends('layouts.admin')

@section('title', 'Catat Setoran')

@section('content')
    <x-page-header title="Catat Setoran" description="Nomor transaksi dan total berat dibuat otomatis oleh sistem." />

    <x-card>
        <form method="POST" action="{{ route('admin.deposits.store') }}">
            @csrf
            @include('admin.deposits._form')
        </form>
    </x-card>
@endsection
