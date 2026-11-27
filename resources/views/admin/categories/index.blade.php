@extends('layouts.admin')

@section('title', 'Kategori')

@section('content')
    <x-page-header title="Kategori E-Waste" description="Pengelompokan barang elektronik yang disetorkan.">
        <x-button :href="route('admin.categories.create')">+ Tambah Kategori</x-button>
    </x-page-header>

    <x-card :padding="false">
        <x-search-bar :action="route('admin.categories.index')" placeholder="Cari nama kategori..." />

        @if ($categories->isEmpty())
            <x-empty-state icon="tag" title="Kategori tidak ditemukan" />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">Deskripsi</th>
                            <th class="px-5 py-3 text-right">Jumlah Barang</th>
                            <th class="px-5 py-3 text-right">Total Berat</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($categories as $category)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $category->name }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $category->description ?: '—' }}</td>
                                <td class="px-5 py-3 text-right text-gray-600">{{ $category->deposit_items_count }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right text-gray-600">
                                    {{ number_format($category->deposit_items_sum_weight ?? 0, 2, ',', '.') }} kg
                                </td>
                                <td class="px-5 py-3"><x-active-badge :active="$category->is_active" /></td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">
                                    <x-table-actions :edit="route('admin.categories.edit', $category)"
                                        :destroy="route('admin.categories.destroy', $category)" :name="'kategori ' . $category->name" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-5 py-3">{{ $categories->links() }}</div>
        @endif
    </x-card>
@endsection
