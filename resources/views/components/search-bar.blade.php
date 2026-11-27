{{--
    Form pencarian/filter GET di atas tabel.
    <x-search-bar :action="route('admin.categories.index')" placeholder="Cari nama...">
        (filter tambahan, misalnya <select name="type">)
    </x-search-bar>
--}}
@props(['action', 'placeholder' => 'Cari...'])

<form method="GET" action="{{ $action }}" class="flex flex-col gap-2 border-b border-gray-200 p-4 sm:flex-row sm:flex-wrap sm:items-center">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ $placeholder }}"
        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200 sm:max-w-xs">

    {{ $slot }}

    <x-button variant="secondary">Terapkan</x-button>

    @if (request()->query())
        <a href="{{ $action }}" class="text-sm text-gray-500 hover:underline">Reset</a>
    @endif
</form>
