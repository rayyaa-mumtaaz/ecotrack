@extends('layouts.user')

@section('title', 'Kategori')

@section('content')
    <x-page-header title="Kategori E-Waste" description="Jenis barang elektronik yang dapat disetorkan ke drop point." />

    @if ($categories->isEmpty())
        <x-card><x-empty-state icon="tag" title="Belum ada kategori" /></x-card>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($categories as $category)
                <x-card>
                    <div class="flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                            <x-icon name="tag" class="h-5 w-5" />
                        </span>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $category->name }}</h3>
                            <p class="mt-1 text-sm text-gray-600">{{ $category->description ?: 'Tidak ada deskripsi.' }}</p>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>
    @endif
@endsection
