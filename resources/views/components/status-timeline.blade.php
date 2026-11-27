{{-- Pelacakan status setoran: Terkumpul -> Diangkut -> Selesai. <x-status-timeline :deposit="$deposit" /> --}}
@props(['deposit'])

@php
    $steps = array_keys(\App\Models\Deposit::STATUSES);
    $currentIndex = array_search($deposit->status, $steps);
    $descriptions = [
        \App\Models\Deposit::STATUS_COLLECTED => 'Diterima ' . ($deposit->receiver?->name ?? 'petugas') . ' di ' . $deposit->dropPoint->name . ' pada ' . $deposit->deposit_date->format('d/m/Y') . '.',
        \App\Models\Deposit::STATUS_SHIPPED => $deposit->pickup
            ? 'Diangkut ' . $deposit->pickup->pickup_date->format('d/m/Y') . ' (' . $deposit->pickup->pickup_number . ').'
            : 'Menunggu pengangkutan dari drop point.',
        \App\Models\Deposit::STATUS_COMPLETED => $deposit->pickup?->partner
            ? 'Diserahkan ke ' . $deposit->pickup->partner->name . ' (' . $deposit->pickup->partner->typeLabel() . ').'
            : 'Diserahkan ke mitra pengelola.',
    ];
@endphp

<ol class="space-y-4">
    @foreach ($steps as $index => $step)
        @php $done = $index <= $currentIndex; @endphp
        <li class="flex gap-3">
            <span @class([
                'flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                'bg-emerald-600 text-white' => $done,
                'bg-gray-200 text-gray-500' => ! $done,
            ])>{{ $index + 1 }}</span>
            <div>
                <p @class(['font-semibold', 'text-gray-900' => $done, 'text-gray-400' => ! $done])>{{ \App\Models\Deposit::STATUSES[$step] }}</p>
                <p @class(['text-sm', 'text-gray-600' => $done, 'text-gray-400' => ! $done])>
                    {{ $done || $index === $currentIndex + 1 ? $descriptions[$step] : 'Belum.' }}
                </p>
            </div>
        </li>
    @endforeach
</ol>
