@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <h1 class="text-xl font-bold text-gray-900">Masuk ke EcoTrack</h1>
    <p class="mt-1 text-sm text-gray-500">Gunakan akun yang diberikan admin.</p>

    <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
        @csrf

        <x-form.input name="email" label="Email" type="email" autocomplete="email" autofocus required />
        <x-form.input name="password" label="Password" type="password" autocomplete="current-password" required />

        <x-button class="w-full">Masuk</x-button>
    </form>

    <p class="mt-4 text-center text-xs text-gray-400">
        Demi keamanan, Anda otomatis logout setelah {{ config('session.idle_timeout') }} menit tidak ada aktivitas.
    </p>
@endsection
