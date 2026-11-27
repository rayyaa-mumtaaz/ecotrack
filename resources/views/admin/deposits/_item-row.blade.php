{{--
    Satu baris barang di form setoran.
    Dipakai untuk baris dari server ($index angka) dan untuk <template> jQuery ($index = '__INDEX__').
--}}
@php
    $field = fn ($key) => "items[{$index}][{$key}]";
    $error = fn ($key) => $errors->first("items.{$index}.{$key}");
    $inputClass = 'block w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:border-emerald-500 focus:ring-emerald-200';
@endphp

<div class="item-row rounded-lg border border-gray-200 bg-gray-50 p-3">
    <div class="grid grid-cols-2 gap-3 md:grid-cols-12">
        <div class="col-span-2 md:col-span-3">
            <label class="mb-1 block text-xs font-medium text-gray-600">Nama barang *</label>
            <input type="text" name="{{ $field('item_name') }}" value="{{ $item['item_name'] ?? '' }}" maxlength="100"
                placeholder="Misal: Handphone" class="{{ $inputClass }} {{ $error('item_name') ? 'border-red-400' : 'border-gray-300' }}">
            @if ($error('item_name')) <p class="mt-1 text-xs text-red-600">{{ $error('item_name') }}</p> @endif
        </div>

        <div class="col-span-2 md:col-span-3">
            <label class="mb-1 block text-xs font-medium text-gray-600">Kategori *</label>
            <select name="{{ $field('category_id') }}" class="{{ $inputClass }} bg-white {{ $error('category_id') ? 'border-red-400' : 'border-gray-300' }}">
                <option value="">-- Pilih --</option>
                @foreach ($categories as $id => $name)
                    <option value="{{ $id }}" @selected((string) ($item['category_id'] ?? '') === (string) $id)>{{ $name }}</option>
                @endforeach
            </select>
            @if ($error('category_id')) <p class="mt-1 text-xs text-red-600">{{ $error('category_id') }}</p> @endif
        </div>

        <div class="md:col-span-1">
            <label class="mb-1 block text-xs font-medium text-gray-600">Jumlah *</label>
            <input type="number" name="{{ $field('quantity') }}" value="{{ $item['quantity'] ?? 1 }}" min="1"
                class="{{ $inputClass }} {{ $error('quantity') ? 'border-red-400' : 'border-gray-300' }}">
            @if ($error('quantity')) <p class="mt-1 text-xs text-red-600">{{ $error('quantity') }}</p> @endif
        </div>

        <div class="md:col-span-2">
            <label class="mb-1 block text-xs font-medium text-gray-600">Berat (kg) *</label>
            <input type="number" name="{{ $field('weight') }}" value="{{ $item['weight'] ?? '' }}" min="0.01" step="0.01"
                placeholder="0,00" class="item-weight {{ $inputClass }} {{ $error('weight') ? 'border-red-400' : 'border-gray-300' }}">
            @if ($error('weight')) <p class="mt-1 text-xs text-red-600">{{ $error('weight') }}</p> @endif
        </div>

        <div class="col-span-2 md:col-span-2">
            <label class="mb-1 block text-xs font-medium text-gray-600">Kondisi *</label>
            <select name="{{ $field('condition') }}" class="{{ $inputClass }} bg-white {{ $error('condition') ? 'border-red-400' : 'border-gray-300' }}">
                <option value="">-- Pilih --</option>
                @foreach (\App\Models\DepositItem::CONDITIONS as $value => $label)
                    <option value="{{ $value }}" @selected(($item['condition'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @if ($error('condition')) <p class="mt-1 text-xs text-red-600">{{ $error('condition') }}</p> @endif
        </div>

        <div class="col-span-2 flex items-end md:col-span-1">
            <button type="button" class="remove-item w-full rounded-lg border border-red-200 px-2 py-2 text-sm text-red-600 hover:bg-red-50" title="Hapus barang">
                Hapus
            </button>
        </div>

        <div class="col-span-2 md:col-span-12">
            <input type="text" name="{{ $field('notes') }}" value="{{ $item['notes'] ?? '' }}" maxlength="255"
                placeholder="Catatan barang (opsional)" class="{{ $inputClass }} border-gray-300">
        </div>
    </div>
</div>
