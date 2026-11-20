{{-- Nama pengguna yang login + tombol logout. Sebelum Stage 3 (login) tampil "Belum login". --}}
@auth
    <div class="flex items-center gap-3">
        <div class="text-right leading-tight">
            <p class="text-sm font-medium text-gray-800">{{ auth()->user()->name }}</p>
            <p class="text-xs text-gray-400">{{ \App\Models\User::ROLES[auth()->user()->role] ?? '' }}</p>
        </div>
        @if (Route::has('logout'))
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-lg p-2 text-gray-500 hover:bg-red-50 hover:text-red-600" title="Logout">
                    <x-icon name="logout" class="h-5 w-5" />
                </button>
            </form>
        @endif
    </div>
@else
    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-500">Belum login</span>
@endauth
