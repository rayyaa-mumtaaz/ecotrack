{{-- Kartu statistik dashboard. Pemakaian: <x-stat-card title="Total Setoran" :value="$total" icon="deposit" /> --}}
@props(['title', 'value', 'icon' => 'box', 'hint' => null])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-white p-5 shadow-sm']) }}>
    <div class="flex items-center justify-between">
        <p class="text-sm font-medium text-gray-500">{{ $title }}</p>
        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
            <x-icon :name="$icon" class="h-5 w-5" />
        </span>
    </div>
    <p class="mt-3 text-2xl font-bold text-gray-900">{{ $value }}</p>
    @if ($hint)
        <p class="mt-1 text-xs text-gray-400">{{ $hint }}</p>
    @endif
</div>
