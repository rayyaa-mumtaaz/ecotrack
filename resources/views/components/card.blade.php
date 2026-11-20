{{-- Kotak konten putih. <x-card title="Judul"> isi </x-card> --}}
@props(['title' => null, 'padding' => true])

<section {{ $attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-white shadow-sm']) }}>
    @if ($title || isset($actions))
        <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-5 py-4">
            <h2 class="font-semibold text-gray-900">{{ $title }}</h2>
            {{ $actions ?? '' }}
        </div>
    @endif

    <div @class(['p-5' => $padding])>
        {{ $slot }}
    </div>
</section>
