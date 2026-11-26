<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>@yield('title', 'EcoTrack') — {{ config('app.name') }}</title>

@fonts
@vite(['resources/css/app.css', 'resources/js/app.js'])

{{-- Aset tambahan per halaman, misalnya peta: @push('head') @vite('resources/js/map.js') @endpush --}}
@stack('head')
