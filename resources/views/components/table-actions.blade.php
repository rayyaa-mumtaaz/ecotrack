{{--
    Tombol aksi di baris tabel: Detail (opsional), Ubah, Hapus (dengan konfirmasi jQuery).
    <x-table-actions :show="route(...)" :edit="route(...)" :destroy="route(...)" :name="$category->name" />
--}}
@props(['show' => null, 'edit' => null, 'destroy' => null, 'name' => 'data ini'])

<div class="inline-flex items-center gap-3">
    @if ($show)
        <a href="{{ $show }}" class="text-emerald-700 hover:underline">Detail</a>
    @endif
    @if ($edit)
        <a href="{{ $edit }}" class="text-emerald-700 hover:underline">Ubah</a>
    @endif
    @if ($destroy)
        <form method="POST" action="{{ $destroy }}" data-confirm="Hapus {{ $name }}?">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-red-600 hover:underline">Hapus</button>
        </form>
    @endif
</div>
