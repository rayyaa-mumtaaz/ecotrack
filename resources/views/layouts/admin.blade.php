<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-800 antialiased"
    data-idle-timeout="{{ config('session.idle_timeout') * 60 }}"
    data-ping-url="{{ route('session.ping') }}">
    <x-sidebar />

    <div class="lg:pl-64">
        {{-- Header atas --}}
        <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-gray-200 bg-white/90 px-4 backdrop-blur sm:px-6">
            <button type="button" data-sidebar-toggle
                class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 lg:hidden" aria-label="Buka menu">
                <x-icon name="menu" class="h-6 w-6" />
            </button>

            <h1 class="truncate text-lg font-semibold">@yield('title', 'Dashboard')</h1>

            <div class="ml-auto flex items-center gap-3">
                <x-user-menu />
            </div>
        </header>

        <main class="p-4 sm:p-6">
            <x-alert />

            @yield('content')
        </main>
    </div>

    @include('partials.idle-warning')

    @stack('scripts')
</body>
</html>
