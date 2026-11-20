{{-- Navigasi user (penyetor). Menu yang route-nya belum dibuat tampil sebagai "Segera". --}}
@php
    $menus = [
        ['label' => 'Riwayat Setoran', 'route' => 'my-deposits.index', 'active' => 'my-deposits.*'],
        ['label' => 'Kategori', 'route' => 'categories.index', 'active' => 'categories.*'],
        ['label' => 'Drop Point', 'route' => 'drop-points.index', 'active' => 'drop-points.*'],
    ];
@endphp

<header class="sticky top-0 z-20 border-b border-gray-200 bg-white/90 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-6xl items-center gap-6 px-4 sm:px-6">
        <a href="{{ Route::has('my-deposits.index') ? route('my-deposits.index') : '#' }}">
            <x-logo />
        </a>

        {{-- Menu desktop --}}
        <nav class="hidden items-center gap-1 md:flex">
            @foreach ($menus as $menu)
                @php
                    $exists = Route::has($menu['route']);
                    $active = request()->routeIs($menu['active']);
                @endphp
                <a href="{{ $exists ? route($menu['route']) : '#' }}"
                    @class([
                        'rounded-lg px-3 py-2 text-sm font-medium transition',
                        'bg-emerald-50 text-emerald-700' => $active,
                        'text-gray-600 hover:bg-gray-100 hover:text-gray-900' => ! $active && $exists,
                        'cursor-not-allowed text-gray-400' => ! $exists,
                    ])
                    @unless ($exists) title="Segera tersedia" @endunless>
                    {{ $menu['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="ml-auto flex items-center gap-3">
            <div class="hidden md:block">
                <x-user-menu />
            </div>
            <button type="button" data-navbar-toggle class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 md:hidden" aria-label="Buka menu">
                <x-icon name="menu" class="h-6 w-6" />
            </button>
        </div>
    </div>

    {{-- Menu mobile, dibuka/tutup dengan jQuery (slideToggle) --}}
    <nav id="navbar-mobile" class="hidden border-t border-gray-200 px-4 py-3 md:hidden">
        @foreach ($menus as $menu)
            @php $exists = Route::has($menu['route']); @endphp
            <a href="{{ $exists ? route($menu['route']) : '#' }}"
                @class([
                    'block rounded-lg px-3 py-2 text-sm font-medium',
                    'bg-emerald-50 text-emerald-700' => request()->routeIs($menu['active']),
                    'text-gray-600' => ! request()->routeIs($menu['active']) && $exists,
                    'text-gray-400' => ! $exists,
                ])>
                {{ $menu['label'] }} @unless ($exists) <span class="text-xs">(segera)</span> @endunless
            </a>
        @endforeach
        <div class="mt-3 border-t border-gray-200 pt-3">
            <x-user-menu />
        </div>
    </nav>
</header>
