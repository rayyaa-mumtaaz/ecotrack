@extends('layouts.admin')

@section('title', 'Tambah Pengguna')

@section('content')
    <x-page-header title="Tambah Pengguna" description="Akun penyetor dibuat oleh admin (tidak ada registrasi publik)." />

    <x-card>
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            @include('admin.users._form')
        </form>
    </x-card>
@endsection
