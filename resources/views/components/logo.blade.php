@props(['light' => false])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2']) }}>
    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-600 text-white">
        <x-icon name="recycle" class="h-5 w-5" />
    </span>
    <span class="text-lg font-bold tracking-tight {{ $light ? 'text-white' : 'text-gray-900' }}">
        Eco<span class="text-emerald-600">Track</span>
    </span>
</span>
