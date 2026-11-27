{{-- Nama pengguna yang login, role, dan tombol logout. Hanya dipakai di halaman yang wajib login. --}}
<div class="flex items-center gap-3">
    <div class="text-right leading-tight">
        <p class="text-sm font-medium text-gray-800">{{ auth()->user()->name }}</p>
        <p class="text-xs text-gray-400">{{ \App\Models\User::ROLES[auth()->user()->role] ?? '' }}</p>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="rounded-lg p-2 text-gray-500 hover:bg-red-50 hover:text-red-600" title="Logout">
            <x-icon name="logout" class="h-5 w-5" />
        </button>
    </form>
</div>
