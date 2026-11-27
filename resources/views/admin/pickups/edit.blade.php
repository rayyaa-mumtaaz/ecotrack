@extends('layouts.admin')

@section('title', 'Ubah Pengangkutan')

@section('content')
    <x-page-header title="Ubah Pengangkutan" :description="$pickup->pickup_number . ' — daftar setoran tidak berubah'" />

    <x-card>
        <form method="POST" action="{{ route('admin.pickups.update', $pickup) }}">
            @csrf
            @method('PUT')
            @include('admin.pickups._fields')

            <div class="mt-6 flex flex-wrap gap-2 border-t border-gray-200 pt-4">
                <x-button>Simpan</x-button>
                <x-button variant="secondary" :href="route('admin.pickups.show', $pickup)">Batal</x-button>
            </div>
        </form>
    </x-card>
@endsection
