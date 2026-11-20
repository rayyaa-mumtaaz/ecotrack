<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-800 antialiased">
    <main class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
        <a href="{{ url('/') }}" class="mb-6">
            <x-logo />
        </a>

        <div class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
            @yield('content')
        </div>

        <p class="mt-6 text-center text-xs text-gray-400">
            &copy; {{ date('Y') }} EcoTrack — Pengelolaan Setoran Limbah Elektronik
        </p>
    </main>

    @stack('scripts')
</body>
</html>
