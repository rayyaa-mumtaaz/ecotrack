@extends('layouts.admin')

@section('title', 'Tambah Mitra')

@section('content')
    <x-page-header title="Tambah Mitra" />

    <x-card>
        <form method="POST" action="{{ route('admin.partners.store') }}">
            @csrf
            @include('admin.partners._form')
        </form>
    </x-card>
@endsection
