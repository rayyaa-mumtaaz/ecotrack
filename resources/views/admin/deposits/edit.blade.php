@extends('layouts.admin')

@section('title', 'Ubah Setoran')

@section('content')
    <x-page-header title="Ubah Setoran" :description="$deposit->deposit_number" />

    <x-card>
        <form method="POST" action="{{ route('admin.deposits.update', $deposit) }}">
            @csrf
            @method('PUT')
            @include('admin.deposits._form')
        </form>
    </x-card>
@endsection
