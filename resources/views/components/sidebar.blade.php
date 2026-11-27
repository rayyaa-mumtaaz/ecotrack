{{-- Navigasi admin. Menu yang route-nya belum dibuat tampil sebagai "Segera". --}}
@php
    $menus = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'home'],
        ['label' => 'Setoran', 'route' => 'admin.deposits.index', 'active' => 'admin.deposits.*', 'icon' => 'deposit'],
        ['label' => 'Pengangkutan', 'route' => 'admin.pickups.index', 'active' => 'admin.pickups.*', 'icon' => 'truck'],
        ['label' => 'Kategori', 'route' => 'admin.categories.index', 'active' => 'admin.categories.*', 'icon' => 'tag'],
        ['label' => 'Drop Point', 'route' => 'admin.drop-points.index', 'active' => 'admin.drop-points.*', 'icon' => 'map-pin'],
        ['label' => 'Mitra', 'route' => 'admin.partners.index', 'active' => 'admin.partners.*', 'icon' => 'recycle'],
        ['label' => 'Pengguna', 'route' => 'admin.users.index', 'active' => 'admin.users.*', 'icon' => 'users'],
        ['label' => 'Laporan', 'route' => 'admin.reports.index', 'active' => 'admin.reports.*', 'icon' => 'chart'],
    ];
@endphp

{{-- Latar gelap saat sidebar terbuka di layar kecil --}}
<div id="sidebar-overlay" data-sidebar-toggle class="fixed inset-0 z-30 hidden bg-gray-900/40 lg:hidden"></div>

<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-gray-200 bg-white transition-transform duration-200 lg:translate-x-0">
    <div class="flex h-16 items-center justify-between border-b border-gray-200 px-5">
        <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : '#' }}">
            <x-logo />
        </a>
        <button type="button" data-sidebar-toggle class="rounded-lg p-1 text-gray-500 hover:bg-gray-100 lg:hidden" aria-label="Tutup menu">
            <x-icon name="x" class="h-5 w-5" />
        </button>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto p-3">
        @foreach ($menus as $menu)
            @php
                $exists = Route::has($menu['route']);
                $active = request()->routeIs($menu['active']);
            @endphp

            <a href="{{ $exists ? route($menu['route']) : '#' }}"
                @class([
                    'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
                    'bg-emerald-50 text-emerald-700' => $active,
                    'text-gray-600 hover:bg-gray-100 hover:text-gray-900' => ! $active && $exists,
                    'cursor-not-allowed text-gray-400' => ! $exists,
                ])>
                <x-icon :name="$menu['icon']" class="h-5 w-5 shrink-0" />
                <span>{{ $menu['label'] }}</span>
                @unless ($exists)
                    <span class="ml-auto rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-gray-400">Segera</span>
                @endunless
            </a>
        @endforeach
    </nav>

    <div class="border-t border-gray-200 p-4 text-xs text-gray-400">
        Panel Admin / Petugas
    </div>
</aside>
