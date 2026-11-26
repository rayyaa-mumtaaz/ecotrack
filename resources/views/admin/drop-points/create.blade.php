@extends('layouts.admin')

@section('title', 'Tambah Drop Point')

@section('content')
    <x-page-header title="Tambah Drop Point" description="Isi data lokasi pengumpulan dan tentukan titiknya di peta." />

    <x-card>
        <form method="POST" action="{{ route('admin.drop-points.store') }}">
            @csrf
            @include('admin.drop-points._form')
        </form>
    </x-card>
@endsection
