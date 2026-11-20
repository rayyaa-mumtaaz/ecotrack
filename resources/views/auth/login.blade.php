@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <h1 class="text-xl font-bold text-gray-900">Masuk ke EcoTrack</h1>
    <p class="mt-1 text-sm text-gray-500">Gunakan akun yang diberikan admin.</p>

    {{-- Proses login (route POST) dibuat pada Stage 3 --}}
    @unless (Route::has('login.store'))
        <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
            Tampilan saja. Proses login diaktifkan pada Stage 3.
        </div>
    @endunless

    <form method="POST" action="{{ Route::has('login.store') ? route('login.store') : '#' }}" class="mt-6 space-y-4">
        @csrf

        <x-form.input name="email" label="Email" type="email" autocomplete="email" autofocus required />
        <x-form.input name="password" label="Password" type="password" autocomplete="current-password" required />

        <label class="flex items-center gap-2 text-sm text-gray-600">
            <input type="checkbox" name="remember" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
            Ingat saya
        </label>

        <x-button class="w-full">Masuk</x-button>
    </form>
@endsection
