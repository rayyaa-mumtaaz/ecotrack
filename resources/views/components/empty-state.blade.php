{{-- Tampilan saat data kosong. <x-empty-state title="Belum ada data" message="..." /> --}}
@props(['title', 'message' => null, 'icon' => 'inbox'])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 py-12 text-center']) }}>
    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
        <x-icon :name="$icon" class="h-6 w-6" />
    </span>
    <p class="mt-4 font-semibold text-gray-700">{{ $title }}</p>
    @if ($message)
        <p class="mt-1 max-w-sm text-sm text-gray-500">{{ $message }}</p>
    @endif
    {{ $slot }}
</div>
