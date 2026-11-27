{{-- Form bersama tambah/ubah setoran. Baris barang ditambah/dihapus dengan jQuery. --}}
@php
    // Setelah validasi gagal pakai input lama; saat ubah pakai barang tersimpan; saat tambah 1 baris kosong
    $items = old('items', $deposit->exists ? $deposit->items->toArray() : [[]]);
@endphp

<div class="grid gap-4 md:grid-cols-2">
    <x-form.select name="user_id" label="Penyetor" :options="$users" :value="$deposit->user_id" placeholder="-- Pilih penyetor --" required />
    <x-form.select name="drop_point_id" label="Drop point" :options="$dropPoints" :value="$deposit->drop_point_id" placeholder="-- Pilih drop point --" required />
    <x-form.input name="deposit_date" label="Tanggal setoran" type="date" :value="$deposit->deposit_date?->toDateString() ?? $deposit->deposit_date"
        :max="now()->toDateString()" required />
    <div>
        <p class="mb-1 block text-sm font-medium text-gray-700">Diterima oleh</p>
        <p class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
            {{ $deposit->receiver?->name ?? auth()->user()->name }}
            <span class="text-xs text-gray-400">(otomatis dari akun yang mencatat)</span>
        </p>
    </div>
    <div class="md:col-span-2">
        <x-form.textarea name="notes" label="Catatan setoran" :value="$deposit->notes" rows="2" />
    </div>
</div>

<div class="mt-6">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
        <h3 class="font-semibold text-gray-900">Detail Barang</h3>
        <p class="text-sm text-gray-600">
            Total berat: <span id="total-weight" class="text-lg font-bold text-emerald-700">0,00</span> kg
        </p>
    </div>

    @error('items')
        <p class="mb-3 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>
    @enderror

    <div id="item-rows" class="space-y-3">
        @foreach ($items as $index => $item)
            @include('admin.deposits._item-row', ['index' => $index, 'item' => $item])
        @endforeach
    </div>

    <button type="button" id="add-item" class="mt-3 rounded-lg border border-dashed border-emerald-400 px-4 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-50">
        + Tambah barang
    </button>

    {{-- Cetakan baris baru untuk jQuery; __INDEX__ diganti nomor unik --}}
    <template id="item-row-template">
        @include('admin.deposits._item-row', ['index' => '__INDEX__', 'item' => []])
    </template>
</div>

<div class="mt-6 flex flex-wrap gap-2 border-t border-gray-200 pt-4">
    <x-button>Simpan Setoran</x-button>
    <x-button variant="secondary" :href="route('admin.deposits.index')">Batal</x-button>
</div>

@push('scripts')
    <script type="module">
        // jQuery: tambah/hapus baris barang dan hitung total berat secara langsung
        let nextIndex = {{ count($items) ? max(array_keys($items)) + 1 : 0 }};

        function updateTotalWeight() {
            let total = 0;
            $('.item-weight').each(function () {
                total += parseFloat($(this).val()) || 0;
            });
            $('#total-weight').text(total.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        }

        function updateRemoveButtons() {
            // Minimal harus ada 1 barang
            $('.remove-item').prop('disabled', $('.item-row').length <= 1).toggleClass('opacity-40', $('.item-row').length <= 1);
        }

        $('#add-item').on('click', function () {
            const html = $('#item-row-template').html().replaceAll('__INDEX__', nextIndex++);
            $(html).hide().appendTo('#item-rows').fadeIn(200).find('input').first().trigger('focus');
            updateRemoveButtons();
        });

        // Event delegation: berlaku juga untuk baris yang ditambahkan belakangan
        $('#item-rows').on('click', '.remove-item', function () {
            $(this).closest('.item-row').fadeOut(150, function () {
                $(this).remove();
                updateTotalWeight();
                updateRemoveButtons();
            });
        });

        $('#item-rows').on('input', '.item-weight', updateTotalWeight);

        updateTotalWeight();
        updateRemoveButtons();
    </script>
@endpush
