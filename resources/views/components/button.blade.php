{{--
    Tombol dengan gaya konsisten.
    <x-button>Simpan</x-button>
    <x-button variant="secondary" href="{{ route('...') }}">Batal</x-button>
    <x-button variant="danger" type="submit">Hapus</x-button>
--}}
@props(['variant' => 'primary', 'href' => null, 'type' => 'submit'])

@php
    $variants = [
        'primary' => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500',
        'secondary' => 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:ring-gray-400',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    ];

    $classes = 'inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2 '
        . ($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
