{{-- Badge status aktif/nonaktif. Pemakaian: <x-active-badge :active="$dropPoint->is_active" /> --}}
@props(['active'])

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ' . ($active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500')]) }}>
    {{ $active ? 'Aktif' : 'Nonaktif' }}
</span>
