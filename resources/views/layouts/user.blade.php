<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="flex min-h-screen flex-col bg-gray-50 font-sans text-gray-800 antialiased"
    data-idle-timeout="{{ config('session.idle_timeout') * 60 }}"
    data-ping-url="{{ route('session.ping') }}">
    <x-navbar />

    <main class="mx-auto w-full max-w-6xl flex-1 p-4 sm:p-6">
        <x-alert />

        @yield('content')
    </main>

    <footer class="border-t border-gray-200 bg-white py-4 text-center text-xs text-gray-400">
        &copy; {{ date('Y') }} EcoTrack — Pengelolaan Setoran Limbah Elektronik
    </footer>

    @include('partials.idle-warning')

    @stack('scripts')
</body>
</html>
