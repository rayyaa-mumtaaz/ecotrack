{{-- Form bersama tambah/ubah mitra --}}
<div class="grid gap-4 lg:grid-cols-2">
    <x-form.input name="name" label="Nama mitra" :value="$partner->name" maxlength="150" required />
    <x-form.select name="type" label="Tipe mitra" :options="\App\Models\Partner::TYPES" :value="$partner->type" required />
    <x-form.input name="contact_person" label="Nama kontak" :value="$partner->contact_person" maxlength="100" />
    <x-form.input name="phone" label="Telepon" :value="$partner->phone" maxlength="20" />
    <x-form.input name="email" label="Email" type="email" :value="$partner->email" />
    <x-form.input name="address" label="Alamat" :value="$partner->address" maxlength="255" required />
    <div class="lg:col-span-2">
        <x-form.textarea name="description" label="Deskripsi (misalnya jenis barang yang diolah)" :value="$partner->description" />
    </div>
    <x-form.checkbox name="is_active" label="Aktif (dapat dipilih saat mencatat pengangkutan)" :checked="$partner->is_active" />
</div>

<div class="mt-6 flex flex-wrap gap-2 border-t border-gray-200 pt-4">
    <x-button>Simpan</x-button>
    <x-button variant="secondary" :href="route('admin.partners.index')">Batal</x-button>
</div>
