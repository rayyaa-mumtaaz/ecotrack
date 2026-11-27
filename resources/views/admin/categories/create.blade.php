@extends('layouts.admin')

@section('title', 'Tambah Kategori')

@section('content')
    <x-page-header title="Tambah Kategori" />

    <x-card>
        <form method="POST" action="{{ route('admin.categories.store') }}">
            @csrf
            @include('admin.categories._form')
        </form>
    </x-card>
@endsection
