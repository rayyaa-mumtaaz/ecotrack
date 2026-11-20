{{-- Badge status setoran. Pemakaian: <x-status-badge :status="$deposit->status" /> --}}
@props(['status'])

@php
    $classes = [
        \App\Models\Deposit::STATUS_COLLECTED => 'bg-blue-100 text-blue-700',
        \App\Models\Deposit::STATUS_SHIPPED => 'bg-amber-100 text-amber-700',
        \App\Models\Deposit::STATUS_COMPLETED => 'bg-green-100 text-green-700',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ' . ($classes[$status] ?? 'bg-gray-100 text-gray-700')]) }}>
    {{ \App\Models\Deposit::STATUSES[$status] ?? $status }}
</span>
