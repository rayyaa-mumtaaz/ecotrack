@extends('layouts.admin')

@section('title', 'Ubah Pengguna')

@section('content')
    <x-page-header title="Ubah Pengguna" :description="$user->name" />

    <x-card>
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')
            @include('admin.users._form')
        </form>
    </x-card>
@endsection
