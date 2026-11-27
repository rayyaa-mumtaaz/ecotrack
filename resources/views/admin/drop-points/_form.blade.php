{{-- Form bersama untuk tambah dan ubah drop point. Dipakai lewat @include. --}}
<div class="grid gap-6 lg:grid-cols-2">
    <div class="space-y-4">
        <x-form.input name="name" label="Nama drop point" :value="$dropPoint->name" maxlength="100" required />
        <x-form.input name="address" label="Alamat" :value="$dropPoint->address" maxlength="255" required />

        <div>
            <label for="description" class="mb-1 block text-sm font-medium text-gray-700">Deskripsi</label>
            <textarea id="description" name="description" rows="3"
                @class([
                    'block w-full rounded-lg border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2',
                    'border-red-400 focus:ring-red-200' => $errors->has('description'),
                    'border-gray-300 focus:border-emerald-500 focus:ring-emerald-200' => ! $errors->has('description'),
                ])>{{ old('description', $dropPoint->description) }}</textarea>
            @error('description')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <x-form.input name="latitude" label="Latitude" type="number" step="any" :value="$dropPoint->latitude" placeholder="-8.1654" />
            <x-form.input name="longitude" label="Longitude" type="number" step="any" :value="$dropPoint->longitude" placeholder="113.7166" />
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1"
                class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                @checked(old('_token') ? old('is_active') : $dropPoint->is_active)>
            Aktif (tampil di peta penyetor)
        </label>
    </div>

    <div>
        <p class="mb-1 text-sm font-medium text-gray-700">Titik lokasi</p>
        <p class="mb-2 text-xs text-gray-500">Klik peta untuk menaruh titik, lalu geser bila perlu. Koordinat terisi otomatis.</p>

        <div id="drop-point-picker" class="isolate h-80 w-full rounded-lg border border-gray-300"
            data-lat="{{ old('latitude', $dropPoint->latitude) }}"
            data-lng="{{ old('longitude', $dropPoint->longitude) }}"
            data-center-lat="{{ \App\Models\DropPoint::MAP_CENTER[0] }}"
            data-center-lng="{{ \App\Models\DropPoint::MAP_CENTER[1] }}"></div>

        <div class="mt-2 flex flex-wrap gap-2">
            <x-button type="button" variant="secondary" id="picker-my-location">Gunakan lokasi saya</x-button>
            <x-button type="button" variant="secondary" id="picker-clear">Hapus titik</x-button>
        </div>
    </div>
</div>

<div class="mt-6 flex flex-wrap gap-2 border-t border-gray-200 pt-4">
    <x-button>Simpan</x-button>
    <x-button variant="secondary" :href="route('admin.drop-points.index')">Batal</x-button>
</div>

@push('head')
    @vite('resources/js/map.js')
@endpush
