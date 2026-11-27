{{-- Form bersama tambah/ubah kategori --}}
<div class="max-w-xl space-y-4">
    <x-form.input name="name" label="Nama kategori" :value="$category->name" maxlength="100" required />
    <x-form.textarea name="description" label="Deskripsi" :value="$category->description" />
    <x-form.checkbox name="is_active" label="Aktif (dapat dipilih saat mencatat setoran)" :checked="$category->is_active" />
</div>

<div class="mt-6 flex flex-wrap gap-2 border-t border-gray-200 pt-4">
    <x-button>Simpan</x-button>
    <x-button variant="secondary" :href="route('admin.categories.index')">Batal</x-button>
</div>
