{{-- Ditampilkan saat abort(403), misalnya user membuka halaman admin --}}
@extends('layouts.guest')

@section('title', 'Akses Ditolak')

@section('content')
    <x-empty-state icon="alert" title="Akses ditolak (403)"
        :message="$exception->getMessage() ?: 'Anda tidak memiliki akses ke halaman ini.'">
        <div class="mt-6">
            @auth
                <x-button :href="\App\Http\Controllers\AuthController::homeRoute()">Kembali ke halaman saya</x-button>
            @else
                <x-button :href="route('login')">Ke halaman login</x-button>
            @endauth
        </div>
    </x-empty-state>
@endsection
