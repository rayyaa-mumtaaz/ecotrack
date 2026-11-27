{{-- Form bersama tambah/ubah pengguna --}}
<div class="grid max-w-3xl gap-4 sm:grid-cols-2">
    <x-form.input name="name" label="Nama lengkap" :value="$user->name" maxlength="255" required />
    <x-form.input name="email" label="Email" type="email" :value="$user->email" required />

    <x-form.input name="password" label="Password" type="password" autocomplete="new-password"
        :placeholder="$user->exists ? 'Kosongkan jika tidak diganti' : 'Minimal 8 karakter'" :required="! $user->exists" />
    <x-form.input name="password_confirmation" label="Ulangi password" type="password" autocomplete="new-password" />

    <x-form.select name="role" label="Role" :options="\App\Models\User::ROLES" :value="$user->role" required />

    {{-- Hanya untuk admin/petugas; ditampilkan/disembunyikan dengan jQuery sesuai pilihan role --}}
    <div id="drop-point-field">
        <x-form.select name="drop_point_id" label="Drop point tempat bertugas" :options="$dropPoints"
            :value="$user->drop_point_id" placeholder="-- Belum ditugaskan --" />
        <p class="mt-1 text-xs text-gray-500">Petugas tampil di halaman dan peta drop point.</p>
    </div>
</div>

<div class="mt-6 flex flex-wrap gap-2 border-t border-gray-200 pt-4">
    <x-button>Simpan</x-button>
    <x-button variant="secondary" :href="route('admin.users.index')">Batal</x-button>
</div>

@push('scripts')
    <script type="module">
        // jQuery: field drop point hanya muncul jika role = admin
        function toggleDropPointField() {
            $('#drop-point-field').toggle($('#role').val() === '{{ \App\Models\User::ROLE_ADMIN }}');
        }

        $('#role').on('change', toggleDropPointField);
        toggleDropPointField();
    </script>
@endpush
