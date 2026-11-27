{{-- Form bersama tambah/ubah pengguna --}}
<div class="grid max-w-3xl gap-4 sm:grid-cols-2">
    <x-form.input name="name" label="Nama lengkap" :value="$user->name" maxlength="255" required />
    <x-form.input name="email" label="Email" type="email" :value="$user->email" required />

    <x-form.input name="password" label="Password" type="password" autocomplete="new-password"
        :placeholder="$user->exists ? 'Kosongkan jika tidak diganti' : 'Minimal 8 karakter'" :required="! $user->exists" />
    <x-form.input name="password_confirmation" label="Ulangi password" type="password" autocomplete="new-password" />

    @if (auth()->user()->isSuperadmin())
        <x-form.select name="role" label="Role" :options="\App\Models\User::ROLES" :value="$user->role" required />

        {{-- Hanya untuk petugas; ditampilkan/disembunyikan dengan jQuery sesuai pilihan role --}}
        <div id="drop-point-field">
            <x-form.select name="drop_point_id" label="Drop point tempat bertugas" :options="$dropPoints"
                :value="$user->drop_point_id" placeholder="-- Belum ditugaskan --" />
            <p class="mt-1 text-xs text-gray-500">Petugas hanya dapat mengelola setoran di drop point ini.</p>
        </div>
    @else
        {{-- Petugas hanya dapat membuat/mengubah akun penyetor --}}
        <div class="sm:col-span-2 rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-600">
            Role: <span class="font-medium text-gray-900">Penyetor</span>. Akun petugas hanya dapat dibuat oleh superadmin.
        </div>
    @endif
</div>

<div class="mt-6 flex flex-wrap gap-2 border-t border-gray-200 pt-4">
    <x-button>Simpan</x-button>
    <x-button variant="secondary" :href="route('admin.users.index')">Batal</x-button>
</div>

@if (auth()->user()->isSuperadmin())
@push('scripts')
    <script type="module">
        // jQuery: field drop point hanya muncul jika role = admin (petugas)
        function toggleDropPointField() {
            $('#drop-point-field').toggle($('#role').val() === '{{ \App\Models\User::ROLE_ADMIN }}');
        }

        $('#role').on('change', toggleDropPointField);
        toggleDropPointField();
    </script>
@endpush
@endif
