@extends('layouts.admin')

@section('title', 'Ubah Drop Point')

@section('content')
    <x-page-header title="Ubah Drop Point" :description="$dropPoint->name" />

    <x-card>
        <form method="POST" action="{{ route('admin.drop-points.update', $dropPoint) }}">
            @csrf
            @method('PUT')
            @include('admin.drop-points._form')
        </form>
    </x-card>
@endsection
