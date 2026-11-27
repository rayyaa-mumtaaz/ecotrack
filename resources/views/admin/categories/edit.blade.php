@extends('layouts.admin')

@section('title', 'Ubah Kategori')

@section('content')
    <x-page-header title="Ubah Kategori" :description="$category->name" />

    <x-card>
        <form method="POST" action="{{ route('admin.categories.update', $category) }}">
            @csrf
            @method('PUT')
            @include('admin.categories._form')
        </form>
    </x-card>
@endsection
