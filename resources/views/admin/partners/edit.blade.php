@extends('layouts.admin')

@section('title', 'Ubah Mitra')

@section('content')
    <x-page-header title="Ubah Mitra" :description="$partner->name" />

    <x-card>
        <form method="POST" action="{{ route('admin.partners.update', $partner) }}">
            @csrf
            @method('PUT')
            @include('admin.partners._form')
        </form>
    </x-card>
@endsection
